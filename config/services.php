<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY')
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),        // Your Google Client ID
        'client_secret' => env('GOOGLE_CLIENT_SECRET'), // Your Google Client Secret
        'redirect' => env('GOOGLE_CALLBACK_URL'),      // Your Google Redirect URL
    ],
    'facebook' => [
        'client_id' =>  env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' =>   env('FACEBOOK_CALLBACK_URL')
    ],

    'cloudfront_domain' => env('CLOUDFRONT_DOMAIN'),

    'apple_iap' => [
        'shared_secret' => env('APPLE_IAP_SHARED_SECRET'),
        'production_url' => env('APPLE_IAP_VERIFY_RECEIPT_URL', 'https://buy.itunes.apple.com/verifyReceipt'),
        'sandbox_url' => env('APPLE_IAP_SANDBOX_VERIFY_RECEIPT_URL', 'https://sandbox.itunes.apple.com/verifyReceipt'),
        'bundle_id' => env('APPLE_IAP_BUNDLE_ID'),
        'root_certificate_path' => env('APPLE_IAP_ROOT_CERT_PATH') ?: resource_path('certificates/AppleRootCA-G3.pem'),
    ],

    'razorpay' => [
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

];
