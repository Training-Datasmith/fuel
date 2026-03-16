<?php

declare(strict_types=1);
/**
 * Fuel is a fast, lightweight, community driven PHP 8.2+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

// Bootstrap the framework - THIS LINE NEEDS TO BE FIRST!
require COREPATH.'bootstrap.php';

// Add framework overload classes here
\Autoloader::add_classes([
    // Example: 'View' => APPPATH.'classes/myview.php',
]);

// Register the autoloader
\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
$env = $_SERVER['FUEL_ENV'] ?? $_ENV['FUEL_ENV'] ?? getenv('FUEL_ENV') ?: Fuel::DEVELOPMENT;
$allowedEnvs = [Fuel::DEVELOPMENT, Fuel::TEST, Fuel::STAGING, Fuel::PRODUCTION];
Fuel::$env = in_array($env, $allowedEnvs, true) ? $env : Fuel::DEVELOPMENT;

// Initialize the framework with the config file.
\Fuel::init('config.php');
