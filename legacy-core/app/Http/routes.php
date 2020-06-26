<?php

if (App::environment('production')) {
    URL::forceSchema('https');
}

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

require __DIR__ . '/Routes/public.php';
require __DIR__ . '/Routes/auth.php';
require __DIR__ . '/Routes/web.php';
require __DIR__ . '/Routes/api.php';
require __DIR__ . '/Routes/external.php';
require __DIR__ . '/Routes/controlpad.php';
require __DIR__ . '/Routes/spa.php';
