<?php

return [
    'connection' => 'tollerus', // name your package uses
    'table_prefix' => 'tollerus_', // '' to disable
    /**
     * This should be between 1 and 5. If number of IDs overflows
     * this width, everything will still function but you'll have
     * a cosmetic problem of inconsistent ID lengths. 5 is the
     * hard maximum because after that it overflows a PHP int.
     */
    'global_id_digits' => 4,
    /**
     * List of word class names that will be suggested as lexeme
     * sources when setting up auto-inflection. This list is
     * assistive, not restrictive, and can be worked around. If
     * it's not correct or exhaustive, UX is only mildly
     * degraded.
     */
    'particle_word_classes' => [
        'combining form',
        'prefix',
        'suffix',
        'affix',
        'root',
        'particle',
    ],
    
    /*
     * Advanced: use this if you want the Tollerus DB connection
     * to differ from your default DB connection.
     */
    'connection_overrides' => [
        'modes' => ['NO_AUTO_VALUE_ON_ZERO'],
        /*
        'driver' => 'mysql',
        'url' => env('DB_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
        */
    ],
];

