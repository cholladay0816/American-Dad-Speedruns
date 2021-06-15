<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    'subscription' => [
        'council' => env('SUBSCRIPTION_COUNCIL'),
        ]
];
