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
    return response()->json('Controlpad. Orders API V0.0');
});

$router->group(['namespace' => 'V0', 'middleware' => 'auth'], function () use ($router) {
    /**
     * ORDERS
     */
    $router->get('/orders', 'OrdersController@index');
    $router->get('/buyers/{id}/orders', 'OrdersController@ordersBySellerId');
    $router->get('/sellers/{id}/orders', 'OrdersController@ordersByBuyerId');
    $router->get('/orders/{id}', 'OrdersController@orderById');
    $router->patch('/orders/{id}', 'OrdersController@edit');
    $router->patch('/orders', 'OrdersController@editOrders');
    /**
     * TRACKING
     */
    $router->post('/tracking', 'TrackingController@create');
    $router->delete('/tracking/{id}', 'TrackingController@delete');
});

$router->group(['namespace' => 'V0', 'prefix' => '/api/v0', 'middleware' => 'auth'], function () use ($router) {
    /**
     * COUPONS
     */
    $router->get('/coupons', 'CouponController@index');
    $router->get('/coupons/{id}', 'CouponController@show');
    $router->post('/coupons', 'CouponController@create');
    $router->delete('/coupons/{id}', 'CouponController@delete');
    /**
     * ORDERS
     */
    $router->get('/orders', 'OrdersController@index');
    $router->get('/buyers/{id}/orders', 'OrdersController@ordersBySellerId');
    $router->get('/sellers/{id}/orders', 'OrdersController@ordersByBuyerId');
    $router->get('/orders/by-receipt-id/{receiptId}', 'OrdersController@byReceiptId');
    $router->get('/orders/{id}', 'OrdersController@orderById');
    $router->patch('/orders/{id}', 'OrdersController@edit');
    $router->patch('/orders', 'OrdersController@editOrders');
    $router->put('/orders/{id}/shipping-address', 'OrdersController@editShippingAddress');
    $router->get('/orders/{id}/accept-inventory', 'OrdersController@acceptInventory');
    /**
     * TRACKING
     */
    $router->post('/tracking', 'TrackingController@create');
    $router->delete('/tracking/{id}', 'TrackingController@delete');
    /**
     * Shipping Rate
     */
    $router->get('/shipping-rate-estimate', 'ShippingRateController@estimate');
});

// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0', 'middleware' => 'public'], function () use ($router) {
    /**
    * Carts
    */
    $router->get('/carts', 'CartController@index');
    $router->get('/carts/{pid}', 'CartController@show');
    $router->get('/carts/{pid}/empty', 'CartController@empty');
    $router->post('/carts', 'CartController@create');
    $router->post('/carts/{pid}/lines', 'CartController@addLines');
    $router->patch('/carts/{pid}/lines', 'CartController@patchLines');
    $router->post('/carts/{pid}/apply-coupon', 'CartController@applyCoupon');
    $router->delete('/carts/{pid}', 'CartController@delete');
    $router->get('/carts/{pid}/estimate-shipping', 'CartController@estimateShipping');
    $router->post('/carts/{cartPid}/create-checkout', 'CheckoutController@createFromCart');
    $router->post('/carts/{cartPid}/create-invoice', 'InvoiceController@createFromCart');
    /**
    * Cartlines
    */
    $router->patch('/cartlines/{pid}', 'CartController@patchCartline');
    $router->delete('/cartlines/{pid}', 'CartController@deleteCartline');
    /**
     * Checkout
     */
    $router->post('/checkouts', 'CheckoutController@create');
    $router->get('/checkouts/{pid}', 'CheckoutController@show');
    $router->patch('/checkouts/{pid}', 'CheckoutController@update');
    $router->post('/checkouts/{pid}/process', 'CheckoutController@process');
    $router->delete('/checkouts/{pid}', 'CheckoutController@delete');
    /**
    * Invoice
    */
    $router->get('/invoices/token/{token}', 'InvoiceController@showByToken');
    $router->post('/invoices/{token}/create-checkout', 'CheckoutController@createFromInvoice');
});

// Monitoring routes

$router->get('/ping', function () {
    return response()->json("Ping Successful")->header('Cache-Control', 'private, no-cache');
});
$router->group(['prefix' => '/monitoring', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/ping', 'MonitoringController@ping');
});
