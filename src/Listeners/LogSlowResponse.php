<?php

namespace audunru\MemoryUsage\Listeners;

use audunru\MemoryUsage\Helpers\TimeHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class LogSlowResponse
{
    public function __construct(protected TimeHelper $timeHelper)
    {
    }

    public function handle(RequestHandled $event)
    {
        $ignorePatterns = config('memory-usage.ignore_patterns', []);

        if ($event->request->is($ignorePatterns)) {
            return;
        }

        $responseTime = $this->timeHelper->getResponseTime();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', []);
            $slowResponseLimit = Arr::get($options, 'slow_response_limit', 1);

            if ($responseTime > $slowResponseLimit && $event->request->is($patterns)) {
                $channel = Arr::get($options, 'channel', null);
                $level = Arr::get($options, 'level', 'warning');

                Log::channel($channel)->log($level, sprintf('Response time %01.2f s for %s is greater than limit of %01.2f s', $responseTime, $event->request->getPathInfo(), $slowResponseLimit));
            }
        }
    }
}
