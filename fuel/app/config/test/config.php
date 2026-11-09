<?php
/**
 * Test environment overrides.
 */

return array(
	'base_url' => '/',
	'log_threshold' => \Fuel::L_INFO,
	'always_load' => array(
		'packages' => array('orm', 'auth', 'email', 'parser'),
	),
);
