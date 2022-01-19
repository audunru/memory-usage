<?php

return [
    /*
     * Enable or disable memory logging.
     */
    'enabled' => env('MEMORY_USAGE_ENABLED', true),

    /*
     * Paths to log memory usage for.
     */
    'paths'   => [
        [
            /*
             * The request path, e.g. api/v1/products will be matched against
             * these patterns.
             */
            'patterns'   => ['*'],

            /*
             * Peak memory usage in megabytes must be above limit before logging takes place.
             */
            'limit'   => 100,

            /*
             * Log using this channel. If set to null, Laravel will use the default channel
             * from config/logging.php. You can find the other options, like "stederr" or
             * "syslog" in that file.
             */
            'channel' => null,

            /* Log using one of these levels:
             *
             * - emergency
             * - alert
             * - critical
             * - error
             * - warning
             * - notice
             * - info
             * - debug
            */
            'level'   => 'warning',
        ],
    ],

    /*
     * Paths to always ignore.
     */
    'ignore_patterns' => [
    ],
];
