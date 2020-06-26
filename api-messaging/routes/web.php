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
    return response()->json('Controlpad Email API.');
});
// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/'], function () use ($router) {
    // PRIVATE ROUTES
    $router->group(['middleware' => 'auth'], function () use ($router) {
      $router->get('/emails/{title}', 'PresetEmailController@show');
      $router->get('/emails', 'PresetEmailController@index');
      $router->patch('/emails/{title}', 'PresetEmailController@updateEmail');
      $router->post('/emails', 'EmailsController@create');
      $router->delete('/emails/{title}', 'EmailsController@delete');
      $router->get('send', 'EmailsController@sendEmail');
      $router->get('/variables/{title}', 'EmailsController@emailVariables');
      $router->get('/example/{title}', 'PresetEmailController@showExampleEmail');
      $router->get('/logs/{type}', 'EmailsController@emailLogs');

    });
});

// Monitoring routes

$router->get('/ping', function() {
  return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
  $router->get('/ping', 'MonitoringController@ping');
});
