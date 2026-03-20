<?php

declare(strict_types=1);

/**
 * Example: Using Arr (dot-notation array access) and Input in FuelPHP.
 *
 * These are two of the most commonly used utility classes in FuelPHP.
 * Arr provides safe, deep array access; Input abstracts HTTP inputs.
 *
 * This file is illustrative — it requires the FuelPHP bootstrap to run.
 */

use Fuel\Core\Arr;
use Fuel\Core\Input;

// ─── Arr: dot-notation access ───────────────────────────────────────────────

$config = [
    'database' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'myapp',
        'options'  => ['charset' => 'utf8mb4'],
    ],
    'cache'    => ['driver' => 'redis'],
];

// 1. Safe deep get (returns default on miss, no exception)
$host    = Arr::get($config, 'database.host');            // 'localhost'
$charset = Arr::get($config, 'database.options.charset'); // 'utf8mb4'
$missing = Arr::get($config, 'database.password', 'N/A'); // 'N/A'

echo "{$host} / charset={$charset} / password={$missing}\n";

// 2. Set a nested key
Arr::set($config, 'database.password', 'secret');
echo Arr::get($config, 'database.password') . "\n"; // 'secret'

// 3. Check existence
var_dump(Arr::key_exists($config, 'database.port'));   // true
var_dump(Arr::key_exists($config, 'database.socket')); // false

// 4. Delete a key
Arr::delete($config, 'cache');
var_dump(Arr::key_exists($config, 'cache')); // false

// 5. Flatten to dot-notation keys
$flat = Arr::flatten($config);
// ['database:host' => 'localhost', 'database:port' => 3306, ...]

// ─── Input: HTTP request data ────────────────────────────────────────────────

// These calls work inside a real FuelPHP request context:

// GET /search?q=php&page=2
$query = Input::get('q', '');         // 'php'
$page  = (int) Input::get('page', 1); // 2

// POST body
$email = Input::post('email', '');

// Detect AJAX
if (Input::is_ajax()) {
    // Handle AJAX-specific response
}

// Get client IP (prefers real IP over proxy headers if x-headers are enabled)
$ip = Input::real_ip();
echo "Client IP: {$ip}\n";

// Get a request header
$accept = Input::headers('Accept');
