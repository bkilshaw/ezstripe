<?php

return [
    'stripe_key' => env('STRIPE_KEY'),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'checkout_success_url' => env('CHECKOUT_SUCCESS_URL'),
    'checkout_cancel_url' => env('CHECKOUT_CANCEL_URL'),
    'billing_portal_return_url' => env('BILLING_PORTAL_RETURN_URL'),
];
