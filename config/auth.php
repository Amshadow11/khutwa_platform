<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards
    | Guard منفصل لكل نوع مستخدم — بدلاً من $_SESSION اليدوي
    |--------------------------------------------------------------------------
    |
    | web     → باحثو العمل (جدول users)
    | company → الشركات    (جدول companies)
    |
    */

    'guards' => [
        // Guard للمستخدمين (باحثو العمل)
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Guard للشركات — مستقل تماماً
        'company' => [
            'driver'   => 'session',
            'provider' => 'companies',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    | كل Guard يتصل بجدول مختلف عبر Model مختلف
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        'companies' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Company::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        'companies' => [
            'provider' => 'companies',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];
