<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Email Driver
    |--------------------------------------------------------------------------
    |
    | The default email driver to use for reading emails. Supported: "gmail"
    |
    */

    'default' => env('EMAIL_DRIVER', 'gmail'),

    /*
    |--------------------------------------------------------------------------
    | Email Drivers
    |--------------------------------------------------------------------------
    |
    | Configuration for each email driver. Add new drivers here as they
    | are implemented.
    |
    */

    'drivers' => [
        'gmail' => [
            'access_token' => env('GMAIL_ACCESS_TOKEN'),
        ],
    ],

];
