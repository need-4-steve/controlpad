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
    'prefix'    => '/api/external',
    'namespace' => 'Api\External',
], function () {
    Route::post('authenticate', 'JWTAuthController@authenticate');
    Route::post('logout', 'JWTAuthController@logout');
    Route::post('assert-authorized-domain', 'JWTAuthController@assertAuthorizedDomain');
    Route::get('refresh-token', 'JWTAuthController@refreshToken');
    Route::post('login-as/{userId}', 'JWTAuthController@loginAs');
    Route::post('revert-login-as', 'JWTAuthController@revertLoginAs');
    // Password management
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'ResetPasswordController@reset');
});
