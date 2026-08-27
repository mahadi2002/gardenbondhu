<?php
declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'GardenBondhu'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false) === true || env('APP_DEBUG') === 'true',
    'url'      => rtrim((string) env('APP_URL', ''), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Dhaka'),
    'locale'   => env('APP_LOCALE', 'bn'),
    'key'      => base64_decode((string) env('APP_KEY', ''), true) ?: '',
    'pepper'   => base64_decode((string) env('HASH_PEPPER', ''), true) ?: '',

    'session'  => [
        'cookie'         => env('SESSION_COOKIE_NAME', 'gb_sid'),
        'lifetime_min'   => (int) env('SESSION_LIFETIME_MINUTES', 1440),
        'absolute_days'  => (int) env('SESSION_ABSOLUTE_DAYS', 30),
        'secure'         => (bool) env('SESSION_SECURE', true),
    ],

    'uploads' => [
        'max_bytes' => (int) env('UPLOAD_MAX_BYTES', 4194304),
        'path'      => env('UPLOAD_PATH', 'storage/uploads'),
    ],

    'admin_ip_allowlist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_IP_ALLOWLIST', ''))
    ))),

    'log_level' => env('LOG_LEVEL', 'info'),

    // Blank until someone sets it in .env — the admin inbox at /admin/contact
    // is the real source of truth either way, this is just a heads-up email.
    'support_email' => env('SUPPORT_EMAIL', ''),

    'retention' => [
        'ratelimit_days' => (int) env('RETAIN_RATELIMIT_DAYS', 7),
        'session_days'   => (int) env('RETAIN_SESSION_DAYS', 30),
        'audit_days'     => (int) env('RETAIN_AUDIT_DAYS', 365),
    ],
];
