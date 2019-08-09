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

$router->group(['namespace' => 'Api', 'prefix' => 'api'], function ($router) {
    /** @var \Laravel\Lumen\Routing\Router $router */

    $router->get('login', ['as' => 'login', 'uses' => 'LoginController@login']);

    $router->get('token/refresh', ['as' => 'login', 'uses' => 'LoginController@login']);

    $router->get('registrations', ['as' => 'login', 'uses' => 'LoginController@login']);

    $router->get('sms/resend', ['as' => 'login', 'uses' => 'LoginController@login']);

});
