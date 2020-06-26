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

$router->get('/', function () {
    return response()->json('Controlpad Events API.');
});

// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/'], function () use ($router) {
    // PUBLIC ROUTES
    $router->group(['middleware' => 'public'], function () use ($router) {
        $router->get('/events/{id}', 'EventsController@show');
        $router->get('/events', 'EventsController@index');
    });
    // PRIVATE ROUTES
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/events', 'EventsController@create');
        $router->patch('/events/{id}', 'EventsController@edit');
        $router->delete('/events/{id}', 'EventsController@delete');
    });
});


$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});

$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
