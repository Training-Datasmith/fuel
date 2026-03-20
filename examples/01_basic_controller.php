<?php

declare(strict_types=1);

/**
 * Example: A basic FuelPHP controller and response.
 *
 * In a FuelPHP application, controllers live under fuel/app/classes/controller/.
 * Each public method named action_* is reachable via the router.
 *
 * Place this file at: fuel/app/classes/controller/welcome.php
 * URL: http://yourapp.com/welcome/hello
 *
 * This example is standalone documentation — it requires the FuelPHP bootstrap.
 */

// File: fuel/app/classes/controller/welcome.php

class Controller_Welcome extends \Fuel\Core\Controller
{
    /**
     * Runs before every action in this controller.
     * Useful for authentication checks or shared setup.
     */
    public function before(): void
    {
        parent::before();
        // e.g. check authentication:
        // if (!\Auth::check()) { \Response::redirect('login'); }
    }

    /**
     * GET /welcome/hello
     * Returns a plain-text greeting.
     */
    public function action_hello(): \Fuel\Core\Response
    {
        $name = \Fuel\Core\Input::get('name', 'World');
        return \Fuel\Core\Response::forge('Hello, ' . htmlspecialchars($name) . '!');
    }

    /**
     * GET /welcome/json
     * Returns a JSON response.
     */
    public function action_json(): \Fuel\Core\Response
    {
        $data = ['status' => 'ok', 'message' => 'FuelPHP is running'];

        $response = \Fuel\Core\Response::forge(json_encode($data), 200, [
            'Content-Type' => 'application/json',
        ]);

        return $response;
    }

    /**
     * POST /welcome/redirect
     * Demonstrates redirect after form submission.
     */
    public function action_redirect(): void
    {
        \Fuel\Core\Response::redirect('welcome/hello?name=Redirected');
    }
}
