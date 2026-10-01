<?php

namespace audunru\MemoryUsage\Http\Middleware;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogMemoryUsage
{
    /**
     * Default paths where memory usage is ignored.
     */
    private const array DEFAULT_IGNORE_PATTERNS = [];

    /**
     * Default paths where memory usage logging is enabled.
     */
    private const array DEFAULT_PATTERNS = [];

    /**
     * Default log channel.
     */
    private const ?string DEFAULT_CHANNEL = null;

    /**
     * Default log level.
     */
    private const string DEFAULT_LEVEL = 'warning';

    /**
     * Default environments where memory usage header is added to responses.
     */
    private const array DEFAULT_ENVIRONMENTS = [];

    /**
     * Default memory usage header name.
     */
    private const string DEFAULT_HEADER_NAME = 'memory-usage';

    public function __construct(protected MemoryHelper $memoryHelper) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $ignorePatterns = config('memory-usage.ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);

        if ($request->is($ignorePatterns)) {
            return $response;
        }

        // The body of a streamed response is produced after the headers are sent, so a header value would be incomplete
        if ($response instanceof StreamedResponse || $response instanceof StreamedJsonResponse) {
            return $response;
        }

        $peakUsage = $this->memoryHelper->getPeakUsage();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', self::DEFAULT_PATTERNS);
            $environments = Arr::get($options, 'header.environments', self::DEFAULT_ENVIRONMENTS);
            $isEnabled = App::environment($environments);

            if ($request->is($patterns) && $isEnabled) {
                $headerName = config('memory-usage.header_name', self::DEFAULT_HEADER_NAME);

                $response->headers->set($headerName, $peakUsage);
            }
        }

        return $response;
    }

    /**
     * @SuppressWarnings("unused")
     */
    public function terminate(Request $request, Response $response): void
    {
        $ignorePatterns = config('memory-usage.ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);

        if ($request->is($ignorePatterns)) {
            return;
        }

        $peakUsage = $this->memoryHelper->getPeakUsage();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', self::DEFAULT_PATTERNS);
            $ignorePaths = Arr::get($options, 'ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);
            $limit = Arr::get($options, 'limit');

            if (! is_null($limit) && $peakUsage > $limit && $request->is($patterns) && ! $request->is($ignorePaths)) {
                $channel = Arr::get($options, 'channel', self::DEFAULT_CHANNEL);
                $level = Arr::get($options, 'level', self::DEFAULT_LEVEL);

                Log::channel($channel)->log($level, sprintf('Maximum memory %01.2f MiB used during request for %s is greater than limit of %01.2f MiB', $peakUsage, $request->getPathInfo(), $limit));
            }
        }
    }
}
