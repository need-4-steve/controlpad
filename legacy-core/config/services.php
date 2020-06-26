<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'squiretax' => [
        'api_key'     => env('SQUIRE_API_KEY'),
        'password'    => env('SQUIRE_API_PASS'),
        'merchant_id' => env('SQUIRE_MERCHANT_ID'),
        'user_id'     => env('SQUIRE_USER_ID')
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN', 'sandboxa147995b44cf4930bda9ebdc01b306da.mailgun.org'),
        'secret' => env('MAILGUN_SECRET', 'key-7jqfacss4emn2o2jrtyc8mbm1t-nc6v5'),
    ],

    'ses' => [
        'key'    => env('SES_KEY', 'AKIAIVKSWYWWBXKBR4XA'),
        'secret' => env('SES_SECRET', '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'),
        'region' => env('SES_REGION', 'us-west-2'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'payman' => [
        'url'    => env('PAYMAN_URL'),
        'apiKey' => env('PAYMAN_KEY'),
    ],

    'rollbar' => [
        'access_token' => env('ROLLBAR_TOKEN', '551531a0ead345a69f922dc1a00d9615'),
        'environment'  => 'production',
        'root'         => base_path()
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_CALLBACK_URL'),
        'version'       => env('FACEBOOK_API_VERSION'),
        'api'           => env('FACEBOOK_API_URL', 'https://graph.facebook.com/'.env('FACEBOOK_API_VERSION').'/'),
        'scope'         => [
            'email',
            'public_profile',
            'publish_actions',
            'user_actions.video',
            'user_friends',
            'user_videos'
        ]
    ],

    'instagram' => [
        'client_id'     => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect'      => env('INSTAGRAM_CALLBACK_URL'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_CALLBACK_URL'),
    ],
];
