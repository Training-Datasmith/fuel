<?php
/**
 * Database settings for the test environment (MySQL from the system-test sidecar).
 */

$host = getenv('FUEL_TEST_DB_HOST') ?: '127.0.0.1';
$name = getenv('FUEL_TEST_DB_NAME') ?: 'fuel_cluster_test';
$user = getenv('FUEL_TEST_DB_USER') ?: 'fuel_test';
$pass = getenv('FUEL_TEST_DB_PASS') ?: 'fuel_test_secret';
$port = getenv('FUEL_TEST_DB_PORT');
$dsn = 'mysql:host='.$host.';dbname='.$name.($port ? ';port='.$port : '');

return array(
	'default' => array(
		'type' => 'pdo',
		'connection' => array(
			'dsn' => $dsn,
			'username' => $user,
			'password' => $pass,
		),
	),
);
