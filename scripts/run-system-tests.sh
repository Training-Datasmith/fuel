#!/usr/bin/env bash
# System test: digest-pinned PHP, composer update from corpus index, pin check, app PHPUnit.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

USE_SUDO_DOCKER="${USE_SUDO_DOCKER:-0}"
DOCKER=(docker)
if [[ "$USE_SUDO_DOCKER" == "1" ]]; then
  DOCKER=(sudo docker)
fi

PHP_IMAGE='php:8.2-cli-bookworm@sha256:ce09ca1d4b532edf9b0347b44a1968a80d9175addb581c450ba0c9a43fa0c5ea'

# Official Composer installer checksum (verify before execution).
COMPOSER_INSTALLER_SHA384='c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47'

log() {
  printf '%s\n' "$*" >&2
}

log "Pulling PHP image (digest-pinned)..."
"${DOCKER[@]}" pull "$PHP_IMAGE"

read -r -d '' INNER <<EOF || true
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_NO_INTERACTION=1

apt-get update
apt-get install -y --no-install-recommends git unzip ca-certificates curl
rm -rf /var/lib/apt/lists/*

cd /tmp
curl -fsSL -o composer-setup.php https://getcomposer.org/installer
echo '${COMPOSER_INSTALLER_SHA384}  composer-setup.php' | sha384sum -c -
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php
composer --version

cd /workspace
if [[ -d fuel/vendor ]]; then rm -rf fuel/vendor; fi
composer update --no-interaction --no-security-blocking

php scripts/check-cluster-pins.php

php fuel/vendor/bin/phpunit -c fuel/app/phpunit.xml --colors=never --do-not-cache-result
EOF

set +e
OUTPUT="$("${DOCKER[@]}" run --rm \
  -v "$ROOT:/workspace" \
  -w /workspace \
  "$PHP_IMAGE" \
  bash -lc "$INNER" 2>&1)"
PHPUNIT_EXIT=$?
set -e

printf '%s\n' "$OUTPUT"

if printf '%s\n' "$OUTPUT" | grep -q 'Warning:'; then
  log "FAIL: output contains Warning:"
  exit 1
fi

if ! printf '%s\n' "$OUTPUT" | grep -qE '^OK \([0-9]+ tests?, [0-9]+ assertions?\)$'; then
  log "FAIL: missing full-line PHPUnit OK summary"
  exit 1
fi

if [[ "$PHPUNIT_EXIT" -ne 0 ]]; then
  log "FAIL: container exit code $PHPUNIT_EXIT"
  exit "$PHPUNIT_EXIT"
fi

log "System test passed."
exit 0
