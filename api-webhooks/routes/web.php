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
    return response()->json('Controlpad. Webhook API V0.0');
});

$router->group(['namespace' => 'V0', 'prefix' => '/api/v0', 'middleware' => 'auth'], function () use ($router) {
    /**
     * Webhooks
     */
    $router->get('/webhooks', 'WebhookController@index');
    $router->get('/webhooks/{id}', 'WebhookController@show');
    $router->post('/webhooks', 'WebhookController@create');
    $router->patch('/webhooks/{id}', 'WebhookController@update');
    $router->delete('/webhooks/{id}', 'WebhookController@delete');
});

// Monitoring routes

$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
