<?php

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // ============================================================
        // قاعدة البيانات الجديدة (Laravel)
        // ============================================================
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'khutwa_db'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
            'options'   => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // ============================================================
        // قاعدة البيانات القديمة (للاستيراد فقط)
        // يُستخدم في ImportOldDataSeeder فقط، يُحذف بعد الاستيراد
        // ============================================================
        'old_db' => [
            'driver'    => 'mysql',
            'host'      => env('OLD_DB_HOST', '127.0.0.1'),
            'port'      => env('OLD_DB_PORT', '3306'),
            'database'  => env('OLD_DB_DATABASE', 'company_db'),
            'username'  => env('OLD_DB_USERNAME', 'root'),
            'password'  => env('OLD_DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

    ],

    'migrations' => [
        'table'  => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],

];
