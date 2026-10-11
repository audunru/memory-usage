# memory-usage

Logs peak memory usage and slow response times for HTTP requests from terminable global middleware (after the response is sent), with per-path limits, configurable log channels/levels, and an optional response header for development.

## Service Provider

`MemoryUsageServiceProvider` pushes two optional global middleware onto the HTTP kernel (`src/Http/Middleware/`):

- `LogMemoryUsage` — enabled when `memory-usage.enabled` is true. `handle()` sets the header (not on streamed responses); `terminate()` logs.
- `LogSlowResponse` — enabled when `memory-usage.slow_response_enabled` is true. `terminate()` logs.

Logging runs in `terminate()` so that streamed bodies (sent after `Kernel::handle()` returns) are included. Do not use the `Terminating` event: it has no request/response and the console kernel also dispatches it.

Config key: `memory-usage` (published via `--tag=memory-usage-config`).

## Configuration

All behavior is driven by `config/memory-usage.php`. Key concepts:

- `paths` — array of path rule sets, each with `patterns`, `ignore_patterns`, `limit` (MiB), `slow_response_limit` (seconds), `channel`, `level`, and optional `header.environments`
- `ignore_patterns` — top-level list of paths always skipped by both listeners
- `header_name` — response header name (default `memory-usage`); only injected when the app environment matches `header.environments` for a given path rule

Multiple path rules are evaluated independently per request, allowing different limits and channels for the same path (e.g., a warning at 10 MiB and an emergency alert to Slack at 100 MiB).

## Testing

`composer test` from the package directory.

`$this->get()` calls `Kernel::terminate()`, so feature tests exercise the logging path. `tests/Feature/StreamedResponseTest.php` drives handle → send → terminate by hand to prove measurement happens after streaming.

Tests mock `MemoryHelper` and `TimeHelper` to inject fixed return values, and mock `Log::channel()` to assert the correct channel, level, and message. Feature tests in `tests/Feature/` cover path matching, ignore patterns, multi-rule evaluation, and header injection. Unit tests in `tests/Unit/` cover the two helper classes directly.

The `enabled`/`slow_response_enabled` flags are read at boot time by the service provider, so toggling them in tests after boot has no effect — see the commented-out test in `tests/Feature/MemoryUsageTest.php`.

## Conventions / Gotchas

- Both listeners share the same `paths` config array; `limit` controls memory logging and `slow_response_limit` controls slow-response logging within the same rule.
- The service provider registers the middleware conditionally at boot, so disabling via config at runtime (after the provider has booted) will not remove it.
- `MemoryHelper::getPeakUsage()` returns MiB as a float (bytes ÷ 1024 ÷ 1024); `TimeHelper::getResponseTime()` uses `REQUEST_TIME_FLOAT` from the server bag.
