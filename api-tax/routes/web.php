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
    return response()->json('Tax API');
});


// VERSION 0.0
$router->group(['namespace' => 'V0', 'prefix' => '/api/v0/', 'middleware' => ['auth']], function () use ($router) {

    $router->group(
        ['middleware' => ['role:Admin,Superadmin']],
        function () use ($router) {
            // Tax connection endpoints, admin only for now
            $router->get('/tax-connections', 'TaxConnectionController@index');
            $router->post('/tax-connections', 'TaxConnectionController@create');
            $router->get('/tax-connections/{id}', 'TaxConnectionController@show');
            $router->patch('/tax-connections/{id}', 'TaxConnectionController@update');
        }
    );

    // Tax invoice endpoints
    $router->get('/tax-invoices', 'TaxInvoiceController@index');
    $router->post('/tax-invoices', 'TaxInvoiceController@create');
    $router->post('/tax-invoices/{pid}/refund', 'TaxInvoiceController@refund');
    $router->get('/tax-invoices/{pid}', 'TaxInvoiceController@show');
    $router->patch('/tax-invoices/{pid}', 'TaxInvoiceController@update');
    $router->post('/tax-invoices/{pid}/commit', 'TaxInvoiceController@commit');
    $router->delete('/tax-invoices/{pid}', 'TaxInvoiceController@delete');
});
