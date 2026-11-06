# memory-usage

Logs peak memory usage and slow response times for HTTP requests, with per-path limits, configurable log channels/levels, and an optional response header for development.

## Service Provider

`MemoryUsageServiceProvider::packageBooted()` registers listeners (`src/Listeners/`):

- `memory-usage.enabled` true → resets the peak with `memory_reset_peak_usage()` on `RouteMatched`, and listens to `RequestHandled` with `LogMemoryUsage` (logs, and sets the header)
- `memory-usage.slow_response_enabled` true → listens to `RequestHandled` with `LogSlowResponse`

`RequestHandled` fires when `Kernel::handle()` returns, so a streamed body that is sent later is not measured.

Config key: `memory-usage` (published via `--tag=memory-usage-config`).

## Configuration

All behavior is driven by `config/memory-usage.php`. Key concepts:

- `paths` — array of path rule sets, each with `patterns`, `ignore_patterns`, `limit` (MiB), `slow_response_limit` (seconds), `channel`, `level`, and optional `header.environments`
- `ignore_patterns` — top-level list of paths always skipped by both listeners
- `header_name` — response header name (default `memory-usage`); only injected when the app environment matches `header.environments` for a given path rule

Multiple path rules are evaluated independently per request, allowing different limits and channels for the same path (e.g., a warning at 10 MiB and an emergency alert to Slack at 100 MiB).

## Testing

`composer test` from the package directory.

Tests mock `MemoryHelper` and `TimeHelper` to inject fixed return values, and mock `Log::channel()` to assert the correct channel, level, and message. Feature tests in `tests/Feature/` cover path matching, ignore patterns, multi-rule evaluation, and header injection. `tests/Feature/PeakResetTest.php` checks that the peak is reset on `RouteMatched`. Unit tests in `tests/Unit/` cover the two helper classes directly.

The `enabled`/`slow_response_enabled` flags are read at boot time by the service provider, so toggling them in tests after boot has no effect — see the commented-out test in `tests/Feature/MemoryUsageTest.php`.

## Conventions / Gotchas

- Both listeners share the same `paths` config array; `limit` controls memory logging and `slow_response_limit` controls slow-response logging within the same rule.
- The service provider registers the listeners conditionally at boot, so disabling via config at runtime (after the provider has booted) will not remove them.
- The header is set on every matching response (also file downloads and streamed responses) when the environment is in `header.environments`.
- `MemoryHelper::getPeakUsage()` returns MiB as a float (bytes ÷ 1024 ÷ 1024); `TimeHelper::getResponseTime()` uses `REQUEST_TIME_FLOAT` from the server bag.
