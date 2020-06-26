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
    return response('Controlpad Users API.');
});

// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/', 'middleware' => 'auth'], function () use ($router) {
    // USER
    $router->group(['middleware' => ['owner']], function () use ($router) {
            $router->get('/users/id/{id}', 'UsersController@findById');
            $router->get('/users/{pid}', 'UsersController@findByPid');
            $router->get('/users/{pid}/card-token', 'UsersController@getCardToken');
    });
    $router->group(['middleware' => ['role:Admin,Superadmin']], function () use ($router) {
            $router->get('/users/email/{email}', 'UsersController@findByEmail');
            $router->get('/users/public-id/{publicID}', 'UsersController@findByPublicID');
            $router->get('/users', 'UsersController@index');
            $router->post('/users', 'UsersController@create');
            $router->patch('/users/{pid}', 'UsersController@update');
            $router->patch('/users', 'UsersController@updateStatus');
    });
    $router->group(['middleware' => ['role:Superadmin,Admin,Rep']], function () use ($router) {
            // For now let reps find a user if they are given id and email so they can do rep to rep transfers
            $router->get('/user-by-id-email', 'UsersController@findByIdAndEmail');
    });

    // CUSTOMER
    $router->get('/customers', 'CustomerController@search');
    $router->post('/customers', 'CustomerController@create');
    $router->post('/customers/attach', 'CustomerController@attach');
    $router->post('/customers/attach-by-pid', 'CustomerController@attachByPid');

    // PLAN
    $router->get('/plans/{pid}', 'PlanController@find');
    $router->get('/plans', 'PlanController@index');
    $router->group(['middleware' => ['role:Admin,Superadmin']], function () use ($router) {
        $router->post('/plans', 'PlanController@create');
        $router->patch('/plans/{pid}', 'PlanController@update');
        $router->delete('/plans/{pid}', 'PlanController@delete');
    });

    // SUBSCRIPTION
    $router->group(['middleware' => ['owner']], function () use ($router) {
        $router->get('/users/{pid}/subscription', 'SubscriptionController@findByUser');
    });
    $router->group(['middleware' => ['role:Admin,Superadmin']], function () use ($router) {
        $router->get('/subscriptions/{pid}', 'SubscriptionController@find');
        $router->get('/subscriptions/user/{user_pid}', 'SubscriptionController@findByUser');
        $router->get('/subscriptions', 'SubscriptionController@index');
        $router->patch('/subscriptions/{pid}', 'SubscriptionController@update');
    });

    // STORE SETTINGS
    // pid is the user's pid
    $router->get('/store-settings/{pid}/{key}', 'StoreSettingController@find');
    $router->get('/store-settings/{pid}', 'StoreSettingController@index');
    $router->group(['middleware' => ['owner']], function () use ($router) {
        $router->patch('/store-settings/{pid}', 'StoreSettingController@update');
    });

    // USER SETTINGS
    // pid is the user's pid
    $router->get('/settings/{pid}/{key}', 'SettingController@find');
    $router->get('/settings/{pid}', 'SettingController@index');
    $router->group(['middleware' => ['owner']], function () use ($router) {
        $router->patch('/settings/{pid}', 'SettingController@update');
    });
});
