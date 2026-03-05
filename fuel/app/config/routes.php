<?php

declare(strict_types=1);
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

return [
    /**
     * -------------------------------------------------------------------------
     *  Default route
     * -------------------------------------------------------------------------
     *
     */

    '_root_' => 'welcome/index',

    /**
     * -------------------------------------------------------------------------
     *  Page not found
     * -------------------------------------------------------------------------
     *
     */

    '_404_' => 'welcome/404',

    /**
     * -------------------------------------------------------------------------
     *  Example for Presenter
     * -------------------------------------------------------------------------
     *
     *  A route for showing page using Presenter
     *
     */

    'hello(/:name)?' => ['welcome/hello', 'name' => 'hello'],
];
