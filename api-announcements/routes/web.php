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
    return response()->json('Controlpad. Announcements API V0.0');
});

// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/'], function () use ($router) {
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/announcements/{id}', 'AnnouncementsController@show');
        $router->get('/announcements', 'AnnouncementsController@index');
        $router->post('/announcements', 'AnnouncementsController@create');
        $router->patch('/announcements/{id}', 'AnnouncementsController@edit');
        $router->delete('/announcements/{id}', 'AnnouncementsController@delete');
    });

});

// Monitoring routes

$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
