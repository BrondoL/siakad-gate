<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->group(['middleware' => ['jwt.verify']], function () use ($router) {
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        $router->get('me', 'AuthController@me');
    });
});

$router->group(['prefix' => 'api', 'middleware' => ['jwt.verify', 'authorize']], function () use ($router) {
    $router->get('users', ['as' => 'user.read', 'uses' => 'UserController@index']);
    $router->post('users', ['as' => 'user.create', 'uses' => 'UserController@store']);
    $router->get('users/{user_id}', ['as' => 'user.detail', 'uses' => 'UserController@show']);
    $router->put('users/{user_id}', ['as' => 'user.update', 'uses' => 'UserController@update']);
    $router->delete('users/{user_id}', ['as' => 'user.delete', 'uses' => 'UserController@destroy']);
});
