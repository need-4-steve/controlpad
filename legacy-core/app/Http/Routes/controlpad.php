<?php

/*
|--------------------------------------------------------------------------
| Controlpad routes
|--------------------------------------------------------------------------
|
| These routes are used with the publicly facing controlpad API, for when
| customers have external integrations.
|
*/

Route::group([
    'prefix'    => '/cp/v1',
    'namespace' => 'Controlpad\V1',
], function () {
    Route::group([
        'middleware' => 'setting:register_with_controlpad_api'
    ], function () {
        Route::get('registration/check/{public_id}', 'RegistrationController@checkPublicId');
        Route::get('registration/check-public-id/{public_id}', 'RegistrationController@checkPublicId');
        Route::get('registration/check-email', 'RegistrationController@checkEmail');
        Route::get('join/{token}', 'RegistrationController@registerWithToken');
        Route::post('users/create', 'RegistrationController@createRegistrationToken');
    });

    Route::post('authenticate', 'RegistrationController@authenticate');

    Route::group([
        'middleware' => 'jwt.auth'
    ], function () {
        Route::post('orders', 'OrderController@index');
        Route::post('orders/mcomm', 'OrderController@mcommIndex');
        Route::get('products', 'ProductController@mcommIndex');
        Route::post('returns', 'ReturnController@index');
        Route::post('users/create-full', 'UserController@createFull');
        Route::patch('users', 'UserController@update');
    });
});
