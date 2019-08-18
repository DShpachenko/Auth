<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['namespace' => 'Api', 'prefix' => 'api', 'middleware' => 'lang'], function ($router) {
    /** @var \Laravel\Lumen\Routing\Router $router */

    $router->post('registration',                 'RegisterController@registration');

    $router->post('registration/confirm',         'RegisterController@confirmation');

    $router->post('registration/resending-sms',   'RegisterController@resendingSms');

    $router->post('login',                        'LoginController@login');

    $router->post('forgot',                       'ForgotController@forgot');

    $router->post('forgot/confirm',               'ForgotController@confirmation');

    $router->post('forgot/resending-sms',         'ForgotController@resendingSms');

    $router->post('token/update',                 'TokenController@update');

});
