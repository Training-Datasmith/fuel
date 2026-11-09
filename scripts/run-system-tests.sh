#!/usr/bin/env bash
# System test: digest-pinned PHP + MySQL, composer update, pin check, app PHPUnit.
#
# MySQL is published on 127.0.0.1 with an ephemeral host port (docker port reads it).
# PHPUnit reaches MySQL on the Docker bridge via the sidecar container IP (port 3306).
# Host-only access: 127.0.0.1:<published-port>. host.docker.internal (Docker Engine 20.10+)
# is for processes on the host; loopback publish is not visible via host-gateway.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

USE_SUDO_DOCKER="${USE_SUDO_DOCKER:-0}"
DOCKER=(docker)
if [[ "$USE_SUDO_DOCKER" == "1" ]]; then
  DOCKER=(sudo docker)
fi

PHP_IMAGE='php:8.2-cli-bookworm@sha256:ce09ca1d4b532edf9b0347b44a1968a80d9175addb581c450ba0c9a43fa0c5ea'
MYSQL_IMAGE='mysql:8.4@sha256:a81d043930e51745522bd5c430961cf8286f0a23f0a9a2a6e0108f7ea71b0902'

# Official Composer installer checksum (verify before execution).
COMPOSER_INSTALLER_SHA384='c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47'

MYSQL_CONTAINER="fuel-cluster-mysql-$$"

log() {
  printf '%s\n' "$*" >&2
}

strip_ansi() {
  sed 's/\x1b\[[0-9;]*[a-zA-Z]//g; s/\x1b\][^\x07]*\x07//g'
}

output_has_blocked_messages() {
  local stripped
  stripped="$(printf '%s\n' "$1" | strip_ansi)"
  if printf '%s\n' "$stripped" | grep -q 'Warning:'; then
    return 0
  fi
  if printf '%s\n' "$stripped" | grep -qE '(^| )Deprecated:|: Deprecated:'; then
    return 0
  fi
  if printf '%s\n' "$stripped" | grep -qE 'Runtime (Deprecated code usage|Notice|Recoverable error)'; then
    return 0
  fi
  if printf '%s\n' "$stripped" | grep -qE 'User (Warning|Notice|Deprecated code usage)'; then
    return 0
  fi
  if printf '%s\n' "$stripped" | grep -qE 'Core Warning:'; then
    return 0
  fi
  return 1
}

cleanup() {
  "${DOCKER[@]}" rm -f "$MYSQL_CONTAINER" >/dev/null 2>&1 || true
}
trap cleanup EXIT

log "Pulling digest-pinned images..."
"${DOCKER[@]}" pull "$PHP_IMAGE"
"${DOCKER[@]}" pull "$MYSQL_IMAGE"

log "Starting MySQL sidecar (healthcheck wait)..."
"${DOCKER[@]}" run -d --name "$MYSQL_CONTAINER" \
  -p 127.0.0.1::3306 \
  -e MYSQL_ROOT_PASSWORD=root_secret \
  -e MYSQL_DATABASE=fuel_cluster_test \
  -e MYSQL_USER=fuel_test \
  -e MYSQL_PASSWORD=fuel_test_secret \
  --health-cmd='mysqladmin ping -h 127.0.0.1 -uroot -proot_secret --silent' \
  --health-interval=3s \
  --health-timeout=5s \
  --health-retries=30 \
  --health-start-period=15s \
  "$MYSQL_IMAGE" >/dev/null

MYSQL_HOST_PORT=$("${DOCKER[@]}" port "$MYSQL_CONTAINER" 3306/tcp | head -1 | awk -F: '{print $NF}')
MYSQL_CONTAINER_IP=$("${DOCKER[@]}" inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$MYSQL_CONTAINER")
if [[ -z "$MYSQL_HOST_PORT" || -z "$MYSQL_CONTAINER_IP" ]]; then
  log "FAIL: could not resolve MySQL host port or container IP"
  exit 2
fi
log "MySQL on bridge ${MYSQL_CONTAINER_IP}:3306; host publish 127.0.0.1:${MYSQL_HOST_PORT}."

for _ in $(seq 1 90); do
  if [[ "$("${DOCKER[@]}" inspect --format='{{.State.Health.Status}}' "$MYSQL_CONTAINER" 2>/dev/null || echo starting)" == "healthy" ]]; then
    log "MySQL is healthy."
    break
  fi
  sleep 2
done
if [[ "$("${DOCKER[@]}" inspect --format='{{.State.Health.Status}}' "$MYSQL_CONTAINER")" != "healthy" ]]; then
  log "FAIL: MySQL sidecar did not become healthy"
  exit 2
fi

read -r -d '' INNER <<EOF || true
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_NO_INTERACTION=1
export FUEL_TEST_DB_HOST=${MYSQL_CONTAINER_IP}
export FUEL_TEST_DB_PORT=3306
export FUEL_TEST_DB_NAME=fuel_cluster_test
export FUEL_TEST_DB_USER=fuel_test
export FUEL_TEST_DB_PASS=fuel_test_secret

apt-get update
apt-get install -y --no-install-recommends git unzip ca-certificates curl default-libmysqlclient-dev
rm -rf /var/lib/apt/lists/*
docker-php-ext-install pdo_mysql

cd /tmp
curl -fsSL -o composer-setup.php https://getcomposer.org/installer
echo '${COMPOSER_INSTALLER_SHA384}  composer-setup.php' | sha384sum -c -
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php
composer --version

git config --global --add safe.directory /workspace
cd /workspace
if [[ -d fuel/vendor ]]; then rm -rf fuel/vendor; fi
composer update --no-interaction || {
  echo "COMPOSER_UPDATE_FAILED=1" >&2
  exit 2
}

composer audit || {
  echo "COMPOSER_AUDIT_FAILED=1" >&2
  exit 2
}

php scripts/check-cluster-pins.php

php fuel/vendor/bin/phpunit -c fuel/app/phpunit.xml --colors=never --do-not-cache-result
EOF

set +e
OUTPUT="$("${DOCKER[@]}" run --rm \
  --add-host=host.docker.internal:host-gateway \
  -e FUEL_TEST_DB_HOST="${MYSQL_CONTAINER_IP}" \
  -e FUEL_TEST_DB_PORT=3306 \
  -e FUEL_TEST_DB_NAME=fuel_cluster_test \
  -e FUEL_TEST_DB_USER=fuel_test \
  -e FUEL_TEST_DB_PASS=fuel_test_secret \
  -v "$ROOT:/workspace" \
  -w /workspace \
  "$PHP_IMAGE" \
  bash -lc "$INNER" 2>&1)"
PHPUNIT_EXIT=$?
set -e

printf '%s\n' "$OUTPUT"

if printf '%s\n' "$OUTPUT" | grep -q 'COMPOSER_UPDATE_FAILED=1\|COMPOSER_AUDIT_FAILED=1\|Your requirements could not be resolved\|because they are affected by security advisories'; then
  log "FAIL: composer update/audit did not complete (see output; no audit bypass used)"
  exit 2
fi

if output_has_blocked_messages "$OUTPUT"; then
  log "FAIL: output contains Warning/Deprecated or Fuel Runtime/User/Core notice-class messages"
  exit 1
fi

if ! printf '%s\n' "$OUTPUT" | strip_ansi | grep -qE '^OK \([0-9]+ tests?, [0-9]+ assertions?\)$'; then
  log "FAIL: missing full-line PHPUnit OK summary"
  exit 1
fi

if [[ "$PHPUNIT_EXIT" -ne 0 ]]; then
  log "FAIL: container exit code $PHPUNIT_EXIT"
  exit "$PHPUNIT_EXIT"
fi

log "System test passed."
exit 0
