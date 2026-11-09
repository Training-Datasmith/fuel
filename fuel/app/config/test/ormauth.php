<?php
/**
 * Ormauth configuration for cluster package tests.
 */

return array(
	'table_name' => 'users',
	'remember_me' => array(
		'enabled' => false,
	),
	'guest_login' => true,
	'login_hash_salt' => 'cluster-orm-login-hash-salt',
);
