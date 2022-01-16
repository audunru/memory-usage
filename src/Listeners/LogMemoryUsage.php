<?php

namespace audunru\MemoryUsage\Listeners;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class LogMemoryUsage
{
    public function __construct(protected MemoryHelper $memoryHelper)
    {
    }

    public function handle(RequestHandled $event)
    {
        $ignorePatterns = config('memory-usage.ignore_patterns', []);

        if ($event->request->is($ignorePatterns)) {
            return;
        }

        $peakUsage = $this->memoryHelper->getPeakUsage();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', []);
            $limit = Arr::get($options, 'limit', 0);

            if ($peakUsage > $limit && $event->request->is($patterns)) {
                $channel = Arr::get($options, 'channel', null);
                $level = Arr::get($options, 'level', 'warning');

                Log::channel($channel)->log($level, sprintf('Maximum memory %01.2f MiB used during request for %s is greater than limit of %01.2f MiB', $peakUsage, $event->request->getPathInfo(), $limit));
            }
        }
    }
}
