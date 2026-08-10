<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => env('APP_NAME', 'Happy Tails Pet Store'),
        'env' => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost:8000'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
        'locale' => env('APP_LOCALE', 'en'),
        'currency' => env('APP_CURRENCY', 'INR'),
        'currency_symbol' => env('APP_CURRENCY_SYMBOL', '₹'),
        'key' => env('APP_KEY', ''),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'petshop'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => (string) env('DB_PASSWORD', ''),
        'ssl_ca' => env('DB_SSL_CA', null),
    ],
    'mail' => [
        'mode' => env('MAIL_MODE', 'log'),
        'host' => env('MAIL_HOST', ''),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Happy Tails Pet Store'),
    ],
    'sms' => [
        'mode' => env('SMS_MODE', 'log'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    ],
    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'razorpay'),
        'razorpay_key_id' => env('RAZORPAY_KEY_ID', ''),
        'razorpay_key_secret' => env('RAZORPAY_KEY_SECRET', ''),
        'razorpay_webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
    ],
    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 120),
        'secure' => (bool) env('SESSION_SECURE_COOKIE', false),
    ],
    'developer_tools' => (bool) env('DEVELOPER_TOOLS', false),
    'cron_secret' => env('CRON_SECRET', ''),
];
