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
    return response()->json('Controlpad. Inventory API V0.0');
});

$router->group(['namespace' => 'V0', 'prefix' => '/api/v0', 'middleware' => 'public'], function () use ($router) {
    $router->get('bundles', 'BundleController@index');
});
// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/', 'middleware' => ['corp', 'auth']], function () use ($router) {
    /**
     * BUNDLES
     */
    $router->get('bundles/{id}', 'BundleController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->post('bundles', 'BundleController@create');
            $router->patch('bundles/{id}', 'BundleController@update');
            $router->delete('bundles/{id}', 'BundleController@delete');
        }
    );

    /**
     * PRODUCTS
     */
    $router->group(
        ['middleware' => ['cache']],
        function () use ($router) {
            $router->get('products', 'ProductController@index');
            $router->get('products/{id}', 'ProductController@find');
        }
    );

    $router->group(
        ['middleware' => ['role:Admin,Superadmin,Rep']],
        function () use ($router) {
            $router->post('products', 'ProductController@create');
            $router->patch('products/{id}', 'ProductController@update');
            $router->delete('products/{id}', 'ProductController@delete');
        }
    );

    /**
     * VARIANTS
     */
    $router->get('variants', 'VariantController@index');
    $router->get('variants/{id}', 'VariantController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin,Rep']],
        function () use ($router) {
            $router->post('variants', 'VariantController@create');
            $router->patch('variants/{id}', 'VariantController@update');
            $router->delete('variants/{id}', 'VariantController@delete');
        }
    );

    /**
     * ITEMS
     */
    $router->get('items', 'ItemController@index');
    $router->get('items/{id}', 'ItemController@find');
    $router->get('variants/{id}', 'VariantController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin,Rep']],
        function () use ($router) {
            $router->post('items', 'ItemController@create');
            $router->patch('items/{id}', 'ItemController@update');
            $router->delete('items/{id}', 'ItemController@delete');
        }
    );

    /**
     * INVENTORY
     */
    $router->group(
        ['middleware' => ['role:Admin,Superadmin,Rep']],
        function () use ($router) {
            $router->patch('inventory/{item_id}', 'ItemController@updateInventory');
        }
    );
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->patch('inventory-quantities', 'ItemController@updateInventoryQuantities');
        }
    );

    /**
     * CATEGORY
     */
    $router->group(
        ['middleware' => ['cache:60']],
        function () use ($router) {
            $router->get('category', 'CategoryController@index');
        }
    );
    $router->get('category/{id}', 'CategoryController@find');
    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            $router->post('category', 'CategoryController@create');
            $router->patch('category/{id}', 'CategoryController@update');
            $router->delete('category/{id}', 'CategoryController@delete');
        }
    );

    /**
     * RESERVATIONS
     */
    $router->get('reservations', 'ReservationController@index');
    $router->get('reservations/{reservationGroupId}', 'ReservationController@show');
    $router->post('reservations', 'ReservationController@create');
    $router->put('reservations/{reservationGroupId}', 'ReservationController@update');
    $router->delete('reservations/{reservationGroupId}', 'ReservationController@destroy');
    $router->post('reservations/transfer', 'ReservationController@transfer');
    $router->get('reservations/{reservationGroupId}/refresh', 'ReservationController@refresh');
});


$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/', 'middleware' => ['corp', 'public']], function () use ($router) {
    $router->get('products/slug/{slug}', 'ProductController@findBySlug');
});

// Monitoring routes

$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
