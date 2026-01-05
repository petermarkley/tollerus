<?php

return [
    /**
     * =========================================================
     *                  END-USER-FACING CONFIG
     * =========================================================
     */

    /**
     * URLs will start with this, e.g.:
     * https://example.com/tollerus/?&id=AAA4
     */
    'route_prefix' => 'tollerus',

    /**
     * Locale-specific words for transliteration (the process,
     * and the target writing system). Used in some UI elements.
     */
    'local_transliteration_word' => 'romanization',
    'local_transliteration_target' => 'Roman',

    /**
     * Laravel middleware applied to all Tollerus routes,
     * including those for viewing/browsing.
     *
     * If your project is private but hosted on a public
     * website, you can for example add 'auth' to this list.
     */
    'middleware' => ['web'],

    /**
     * Laravel middleware applied to only admin Tollerus routes,
     * i.e. those for editing or inputting data.
     *
     * If you have no user authentication, remove 'auth'.
     * Conversely, if you have many users and want only admins
     * to edit, you can for example change to 'auth:admin',
     * or whatever fits your needs.
     */
    'admin_middleware' => ['web','auth'],

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

    /**
     * This should be between 1 and 5. If number of IDs overflows
     * this width, everything will still function but you'll have
     * a cosmetic problem of inconsistent ID lengths in output
     * (input doesn't care and trims leading 'A's anyway).
     *
     * 5 is the hard maximum because after that it overflows a
     * PHP int.
     */
    'global_id_digits' => 4,

    /**
     * =========================================================
     *                   SYS-ADMIN CONFIG
     * =========================================================
     */

    /**
     * Should Tollerus dispatch queued jobs, or just run
     * everything synchronously?
     *
     * (If your app's `config('queue.default')` is 'sync'
     * anyway, this doesn't matter.)
     */
    'enable_queue' => true,

    /**
     * =========================================================
     *                    DATABASE CONFIG
     * =========================================================
     */

    /**
     * Maximum size of SVG and TTF font files. Default of
     * 1,048,576 bytes is 1 MiB.
     */
    'max_font_size' => 1048576,

    /**
     * The service provider takes your default DB config array,
     * and builds a new one under this `connection` name that
     * includes the `table_prefix` and `connection_overrides`.
     */
    'connection' => 'tollerus',

    /**
     * The actual database tables for Tollerus will all have
     * names prefixed with this. Set to an empty string '' if
     * you want no prefix.
     */
    'table_prefix' => 'tollerus_',

    /*
     * Advanced: use this if you want the Tollerus DB connection
     * to differ from your default DB connection.
     */
    'connection_overrides' => [
        /**
         * NO_AUTO_VALUE_ON_ZERO allows a language object to
         * exist under ID 'AAAA'. (This is especially important
         * for imported legacy data.)
         */
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

