<?php
/**
 * Simpleauth configuration for cluster package tests.
 */

return array(
	'table_name' => 'users',
	'remember_me' => array(
		'enabled' => false,
	),
	'groups' => array(
		1 => array('name' => 'Users', 'roles' => array('user')),
	),
	'roles' => array(
		'user' => array('comments' => array('read')),
	),
	'guest_login' => false,
	'login_hash_salt' => 'cluster-login-hash-salt',
);
