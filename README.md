# Log Laravel Memory usage

[![Build Status](https://github.com/audunru/memory-usage/actions/workflows/validate.yml/badge.svg)](https://github.com/audunru/memory-usage/actions/workflows/validate.yml)
[![Coverage Status](https://coveralls.io/repos/github/audunru/memory-usage/badge.svg?branch=main)](https://coveralls.io/github/audunru/memory-usage?branch=main)

Log amount of memory used during HTTP requests. The peak memory usage in megabytes will be logged after the response has been sent.

The memory limit is configurable per request path. If you set the limit to 25 MiB for all requests, you will see something like this in your logs:

```
[2022-01-16 10:49:17] local.WARNING: Maximum memory 50.68 MiB used during request for /api/v1/companies/1/products is greater than limit of 25.00 MiB
[2022-01-16 10:49:29] local.WARNING: Maximum memory 50.39 MiB used during request for /api/v1/companies/1 is greater than limit of 25.00 MiB
[2022-01-16 10:49:29] local.WARNING: Maximum memory 60.04 MiB used during request for /api/v1/companies/1/sales is greater than limit of 25.00 MiB
```

Since v0.3.0 you can also log slow responses. If you set the limit to 3 seconds, you you will see something like this in your logs:

```
[2022-01-16 10:49:17] local.WARNING: Response time 5.15 s for /api/v1/companies/1/products is greater than limit of 3.00 s
```

# Installation

## Step 1: Install with Composer

```bash
composer require audunru/memory-usage
```

# Configuration

Publish the configuration file by running:

```php
php artisan vendor:publish --tag=memory-usage-config
```

Please open up the configuration file for further instructions on how to configure logging.

# Long-running workers (Octane, Swoole, RoadRunner)

`memory_get_peak_usage()` returns the highest memory watermark since the PHP **process** started — it never decreases. Under a traditional PHP-FPM setup this is fine: the process dies after each request. Under long-running workers (e.g. Laravel Octane with `--max-requests=500`) the same worker handles hundreds of requests, and the peak from one expensive request would stay elevated for all subsequent requests, causing false alerts.

To avoid this, the package listens to Laravel's `RouteMatched` event and calls `memory_reset_peak_usage()` at the start of each matched request. The reported peak therefore reflects only the work done during that request, matching the label "maximum memory used during request". This requires PHP 8.2+.

# Streamed responses

Memory usage and response time are measured by a terminable middleware, after the response has been sent. For a `StreamedResponse` or `StreamedJsonResponse`, this means the work done while the body streams is included.

The `memory-usage` header is not added to streamed responses. The headers are sent before the body streams, so the value would not include the streaming work.

# Development

## Testing

Run tests:

```bash
composer test
```
