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

$router->get('/', function () use ($router) {
    return "Authentication API";
});

$router->group(['namespace' => 'V0', 'prefix' => '/api/v0'], function ($router) {
    // public apis
    $router->post('/authenticate', 'AuthController@login');

    // User Authenticated Routes
    $router->group(['middleware' => 'auth:api'], function ($router) {


        //API Key Routes
        $router->get('/apikeys', 'ApiKeyController@index');
        $router->get('/apikeys/{app_id}', 'ApiKeyController@show');
        $router->post('/apikeys', 'ApiKeyController@create');
        $router->patch('/apikeys/{app_id}', 'ApiKeyController@update');
        $router->delete('/apikeys/{id}', 'ApiKeyController@delete');

        //User Enpoints
        $router->get('/users/{id}', 'UserController@show');
        $router->patch('/users/{id}', 'UserController@update');

        //Service Endpoints
        $router->get('/services', 'ServiceController@index');
        $router->get('/services/{id}', 'ServiceController@show');
    });

    // Admin Authenticated Routes
    $router->group(['middleware' => 'admin:api'], function ($router) {

        // Tenant Endpoints
        $router->get('/tenants', 'TenantController@index');
        $router->get('/tenants/{id}', 'TenantController@show');
        $router->post('/tenants', 'TenantController@create');
        $router->patch('/tenants/{id}', 'TenantController@update');
        $router->delete('/tenants/{id}', 'TenantController@delete');

        // Service Endpoints


        $router->post('/services', 'ServiceController@create');
        $router->patch('/services/{id}', 'ServiceController@update');
        $router->delete('/services/{id}', 'ServiceController@delete');

        // User Endpoints
        $router->get('/users', 'UserController@index');
        $router->post('/users', 'UserController@create');
        $router->delete('/users/{id}', 'UserController@delete');
    });

    // API Key Authenticated Routes
    $router->group(['middleware' => 'apikey:api'], function ($router) {
        // API Key Routes
        $router->post('/find-tenant-by-domain', 'ApiKeyController@findTenantByDomain');
        $router->group(['prefix' => '/apikeys'], function ($router) {
            $router->post('/auth', 'ApiKeyController@auth');
        });
    });
});

// Monitoring Routes
$router->get('/ping', function () {
    if (DB::connection()->getDatabaseName()) {
        return response()->json('Ping Successful');
    }
    return response()->json('Ping Failed', 500);
});
