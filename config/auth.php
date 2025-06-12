<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Ini default guard dan password broker.
    | Guard 'api' pakai JWT, cocok buat token-based API.
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Guard 'api' pakai driver 'jwt'
    | Guard 'web' tetap pakai session (buat frontend biasa)
    */

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],

    // ⬇️ Tambahin ini biar gak error saat di-loop
    'admin' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],

    'penjual' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],

    'pembeli' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],



    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Gunakan Eloquent dan model App\Models\User
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Opsi reset password. Amanin token expire dan throttle-nya.
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout konfirmasi ulang password (default: 3 jam)
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
