<?php
/**
 * Database settings for the test environment (SQLite).
 */

return array(
	'default' => array(
		'type' => 'pdo',
		'connection' => array(
			'dsn' => 'sqlite:'.APPPATH.'tmp/cluster.sqlite',
			'username' => '',
			'password' => '',
		),
		'charset' => false,
		'identifier' => '"',
	),
);
