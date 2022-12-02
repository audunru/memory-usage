<?php

return [
    /*
     * Enable or disable memory logging.
     */
    'enabled' => env('MEMORY_USAGE_ENABLED', true),

    /*
     * Enable or disable slow response logging.
     */
    'slow_response_enabled' => env('SLOW_RESPONSE_ENABLED', true),

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
             * Paths to ignore. Takes precedence over the 'patterns' option.
             */
            'ignore_patterns' => [],

            /*
             * Peak memory usage in megabytes must be above limit before logging takes place.
             */
            'limit'   => 100,

            /*
             * Response time in seconds must be above limit before logging takes place.
             */
            'slow_response_limit'   => 30.0,

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

            'header' => [
                /*
                 * Environments where memory usage will be added as a header in the response.
                 * Set this to ['local'] to enable the header during development.
                 */
                'environments' => [],
            ],
        ],
    ],

    /*
     * header name added to response if enabled.
     */
    'header_name' => 'memory-usage',

    /*
     * Paths to always ignore.
     */
    'ignore_patterns' => [
    ],
];
