<?php
declare(strict_types=1);

return [
    'driver'      => env('CARRIER_DRIVER', 'mock'),
    'base_url'    => rtrim((string) env('CARRIER_BASE_URL', ''), '/'),
    'app_id'      => env('CARRIER_APP_ID'),
    'app_key'     => env('CARRIER_APP_KEY'),
    'app_secret'  => env('CARRIER_APP_SECRET'),
    'password'    => env('CARRIER_PASSWORD'),
    'product_id'  => env('CARRIER_PRODUCT_ID'),
    'amount'      => (float) env('CARRIER_DAILY_AMOUNT', 2.78),
    'currency'    => env('CARRIER_CURRENCY', 'BDT'),
    'timeout'     => (int) env('CARRIER_TIMEOUT', 15),
    'webhook'     => [
        'secret' => env('CARRIER_WEBHOOK_SECRET'),
        'ips'    => array_values(array_filter(array_map('trim', explode(',', (string) env('CARRIER_WEBHOOK_IPS'))))),
    ],
    'sms'         => [
        'shortcode' => env('CARRIER_SHORTCODE', '16216'),
        'stop_word' => env('CARRIER_STOP_KEYWORD', 'STOP'),
    ],
    'grace_hours'  => (int) env('SUB_GRACE_HOURS', 48),
    'period_hours' => (int) env('SUB_PERIOD_HOURS', 24),
];
