<?php

return [
    'default' => env('EMAIL_DRIVER', 'gmail'),

    'drivers' => [
        'gmail' => [
            'access_token' => env('GMAIL_ACCESS_TOKEN'),
        ],
    ],
];
