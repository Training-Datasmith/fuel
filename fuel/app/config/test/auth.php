<?php
/**
 * Auth configuration for cluster package tests.
 */

return array(
	'driver' => 'Simpleauth',
	'verify_multiple_logins' => false,
	'login_type' => 'username',
	'salt' => 'cluster-test-auth-salt',
	'iterations' => 10,
);
