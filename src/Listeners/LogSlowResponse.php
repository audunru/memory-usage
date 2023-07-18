<?php

namespace audunru\MemoryUsage\Listeners;

use audunru\MemoryUsage\Helpers\TimeHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class LogSlowResponse
{
    /**
     * Default paths where slow responses are ignored.
     */
    private const DEFAULT_IGNORE_PATTERNS = [];

    /**
     * Default paths where memory usage logging is enabled.
     */
    private const DEFAULT_PATTERNS = [];

    /**
     * Default log channel.
     */
    private const DEFAULT_CHANNEL = null;

    /**
     * Default log level.
     */
    private const DEFAULT_LEVEL = 'warning';

    public function __construct(protected TimeHelper $timeHelper)
    {
    }

    public function handle(RequestHandled $event)
    {
        $ignorePatterns = config('memory-usage.ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);

        if ($event->request->is($ignorePatterns)) {
            return;
        }

        $responseTime = $this->timeHelper->getResponseTime();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', self::DEFAULT_PATTERNS);
            $ignorePaths = Arr::get($options, 'ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);
            $slowResponseLimit = Arr::get($options, 'slow_response_limit');

            if (! is_null($slowResponseLimit) && $responseTime > $slowResponseLimit && $event->request->is($patterns) && ! $event->request->is($ignorePaths)) {
                $channel = Arr::get($options, 'channel', self::DEFAULT_CHANNEL);
                $level = Arr::get($options, 'level', self::DEFAULT_LEVEL);

                Log::channel($channel)->log($level, sprintf('Response time %01.2f s for %s is greater than limit of %01.2f s', $responseTime, $event->request->getPathInfo(), $slowResponseLimit));
            }
        }
    }
}
