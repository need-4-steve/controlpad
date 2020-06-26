<?php

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes should only deal with authenticating a user
| or resetting their password. These routes should
| be publicly available.
|
*/

Route::get('/oauth/callback/{driver}', 'LoginController@handleOauthCallback');
