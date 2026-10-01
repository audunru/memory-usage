<?php

namespace audunru\MemoryUsage\Http\Middleware;

use audunru\MemoryUsage\Helpers\TimeHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSlowResponse
{
    /**
     * Default paths where slow responses are ignored.
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

    public function __construct(protected TimeHelper $timeHelper) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
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

        $responseTime = $this->timeHelper->getResponseTime();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', self::DEFAULT_PATTERNS);
            $ignorePaths = Arr::get($options, 'ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);
            $slowResponseLimit = Arr::get($options, 'slow_response_limit');

            if (! is_null($slowResponseLimit) && $responseTime > $slowResponseLimit && $request->is($patterns) && ! $request->is($ignorePaths)) {
                $channel = Arr::get($options, 'channel', self::DEFAULT_CHANNEL);
                $level = Arr::get($options, 'level', self::DEFAULT_LEVEL);

                Log::channel($channel)->log($level, sprintf('Response time %01.2f s for %s is greater than limit of %01.2f s', $responseTime, $request->getPathInfo(), $slowResponseLimit));
            }
        }
    }
}
