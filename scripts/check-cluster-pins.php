#!/usr/bin/env php
<?php
/**
 * Verify installed packages match corpus-pinned source SHAs.
 *
 * Exit 0 when every expected package is present with the correct git reference
 * and repository URL. Exit 1 on any mismatch or missing package.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$installedPath = $root.'/fuel/vendor/composer/installed.json';

if (! is_file($installedPath)) {
    fwrite(STDERR, "Missing {$installedPath}; run composer install first.\n");
    exit(1);
}

$raw = file_get_contents($installedPath);
if ($raw === false) {
    fwrite(STDERR, "Could not read {$installedPath}.\n");
    exit(1);
}

$data = json_decode($raw, true);
if (! is_array($data)) {
    fwrite(STDERR, "Invalid JSON in {$installedPath}.\n");
    exit(1);
}

$packages = isset($data['packages']) && is_array($data['packages'])
    ? $data['packages']
    : $data;

$byName = array();
foreach ($packages as $pkg) {
    if (! is_array($pkg) || empty($pkg['name'])) {
        continue;
    }
    $byName[$pkg['name']] = $pkg;
}

/** @var array<string, array{sha: string, url_contains: string, forbid_url?: string}> */
$expected = array(
    'fuel/auth' => array(
        'sha' => '98e75adaabd7c326860115195e7361b875ef6b22',
        'url_contains' => 'Training-Datasmith/auth',
    ),
    'fuel/docs' => array(
        'sha' => '7a08da1f849d2ff2e206eb7d83d72fbf60f1fb1c',
        'url_contains' => 'Training-Datasmith/docs',
    ),
    'fuel/email' => array(
        'sha' => '5f3df6e18514d98670c58e6b2793023ca019ecaa',
        'url_contains' => 'Training-Datasmith/email',
    ),
    'fuel/oil' => array(
        'sha' => '94261aa0af0e856d9ef2a758acb9ba3b3ca8dc03',
        'url_contains' => 'Training-Datasmith/oil',
    ),
    'fuel/orm' => array(
        'sha' => '53ee8516dc2bac9ca6016c2b4528e2c892330c19',
        'url_contains' => 'Training-Datasmith/orm',
    ),
    'fuel/parser' => array(
        'sha' => '5ba832401572eb5e06f4847bf082a8e548114563',
        'url_contains' => 'Training-Datasmith/parser',
    ),
    'psr/log' => array(
        'sha' => 'bde874f9b44efec5901a6783185c050a3855b0f0',
        'url_contains' => 'Training-Datasmith/log',
    ),
    'fuel/core' => array(
        'sha' => '050c3147b1d889223701b63e22f60e90bf46cc1e',
        'url_contains' => 'fuel/core',
        'forbid_url' => 'Training-Datasmith',
    ),
    'fuelphp/upload' => array(
        'sha' => '73b3abfa317c22d6b4d63588e9601c13623ea6f6',
        'url_contains' => 'fuelphp/upload',
        'forbid_url' => 'Training-Datasmith',
    ),
    'composer/installers' => array(
        'sha' => '12fb2dfe5e16183de69e784a7b84046c43d97e8e',
        'url_contains' => 'composer/installers',
        'forbid_url' => 'Training-Datasmith',
    ),
    'monolog/monolog' => array(
        'sha' => 'bf2403f591c5a431b6e0e1170cbadd3d64499564',
        'url_contains' => 'monolog',
        'forbid_url' => 'Training-Datasmith',
    ),
);

$failures = 0;

foreach ($expected as $name => $spec) {
    $pkgFailures = 0;

    if (! isset($byName[$name])) {
        fwrite(STDERR, "FAIL: package {$name} is not installed.\n");
        $failures++;
        continue;
    }

    $pkg = $byName[$name];
    $source = isset($pkg['source']) && is_array($pkg['source']) ? $pkg['source'] : array();
    $ref = isset($source['reference']) ? strtolower((string) $source['reference']) : '';
    $url = isset($source['url']) ? (string) $source['url'] : '';
    $wantSha = strtolower($spec['sha']);

    if ($ref === '' || strlen($ref) !== 40) {
        fwrite(STDERR, "FAIL: {$name} has no 40-char source.reference (got ".var_export($ref, true).").\n");
        $pkgFailures++;
    } elseif ($ref !== $wantSha) {
        fwrite(STDERR, "FAIL: {$name} reference {$ref} != expected {$wantSha}\n");
        $pkgFailures++;
    }

    if ($spec['url_contains'] !== '' && strpos($url, $spec['url_contains']) === false) {
        fwrite(STDERR, "FAIL: {$name} source.url ".var_export($url, true)." does not contain {$spec['url_contains']}\n");
        $pkgFailures++;
    }

    if (! empty($spec['forbid_url']) && strpos($url, $spec['forbid_url']) !== false) {
        fwrite(STDERR, "FAIL: {$name} must not come from {$spec['forbid_url']} (got ".var_export($url, true).")\n");
        $pkgFailures++;
    }

    if ($pkgFailures > 0) {
        $failures += $pkgFailures;
        continue;
    }

    $version = isset($pkg['version']) ? $pkg['version'] : '?';
    fwrite(STDOUT, "OK {$name} {$version} @ {$ref}\n");
}

if ($failures > 0) {
    fwrite(STDERR, "{$failures} pin check failure(s).\n");
    exit(1);
}

fwrite(STDOUT, "All cluster pins match.\n");
exit(0);
