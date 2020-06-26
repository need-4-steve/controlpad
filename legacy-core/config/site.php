<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site general configutaion
    |--------------------------------------------------------------------------
    |
    | This file is for storage of settings for site
    |
    |
    */

    'company_name'     => env('COMPANY_NAME', 'Controlpad'),
    'testing_password' => 'password2',
    'apex_user_id'     => 1,
    'domain'           => env('SITE_DOMAIN', 'mycontrolpad.com'),
    'customer_service_number' => '1.800.836.4493',
    'customer_service_email' => env('SERVICE_EMAIL', 'service@controlpad.com'),
    'google_tracking_id' =>env('GOOGLE_TRACKING_ID', 'UA-108240165-5')

];
