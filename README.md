# Log Laravel Memory usage

[![Build Status](https://github.com/audunru/memory-usage/actions/workflows/validate.yml/badge.svg)](https://github.com/audunru/memory-usage/actions/workflows/validate.yml)
[![Coverage Status](https://coveralls.io/repos/github/audunru/memory-usage/badge.svg?branch=main)](https://coveralls.io/github/audunru/memory-usage?branch=main)
[![StyleCI](https://github.styleci.io/repos/448512424/shield?branch=main)](https://github.styleci.io/repos/448512424)

Log amount of memory used during HTTP requests. The peak memory usage in megabytes will be logged right before the response is returned.

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

# Development

## Testing

Run tests:

```bash
composer test
```
