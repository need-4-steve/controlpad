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
    return response()->json('Controlpad. Autoship API V0.0');
});


// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/', 'middleware' => ['corp','auth']], function () use ($router) {
    /**
     * MESSAGING
     */
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            // Routes to manually send messages for testing.
            $router->get('mail/reminder/{subscription_pid}', 'MessagingController@sendReminder');
            $router->get('mail/failure/{subscription_pid}', 'MessagingController@sendFailure');
        }
    );

    /**
     * PLANS
     */
    $router->get('plans', 'PlanController@index');
    $router->get('plans/{pid}', 'PlanController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->post('plans', 'PlanController@create');
            $router->patch('plans/{pid}', 'PlanController@update');
            $router->delete('plans/{pid}', 'PlanController@delete');
        }
    );

    /**
     * PROCESS SUBSCRIPTION
     */
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->post('subscriptions/process/{pid}', 'SubscriptionController@processSubscription');
        }
    );

    /**
     * SUBSCRIPTIONS
     */
    $router->get('subscriptions', 'SubscriptionController@index');
    $router->get('subscriptions/{pid}', 'SubscriptionController@find');
    $router->post('subscriptions', 'SubscriptionController@create');
    $router->delete('subscriptions/{pid}', 'SubscriptionController@delete');
    $router->patch('subscriptions/{pid}', 'SubscriptionController@update');

    /**
     * SUBSCRIPTION LINES
     */
    $router->get('subscription-lines/{pid}', 'SubscriptionLineController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->post('subscription-lines', 'SubscriptionLineController@create');
            $router->patch('subscription-lines/{pid}', 'SubscriptionLineController@update');
            $router->delete('subscription-lines/{pid}', 'SubscriptionLineController@delete');
        }
    );
});

// Monitoring routes
$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
