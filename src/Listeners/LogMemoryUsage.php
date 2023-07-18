<?php

namespace audunru\MemoryUsage\Listeners;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class LogMemoryUsage
{
    /**
     * Default paths where memory usage is ignored.
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

    /**
     * Default environments where memory usage header is added to responses.
     */
    private const DEFAULT_ENVIRONMENTS = [];

    /**
     * Default memory usage header name.
     */
    private const DEFAULT_HEADER_NAME = 'memory-usage';

    public function __construct(protected MemoryHelper $memoryHelper)
    {
    }

    public function handle(RequestHandled $event)
    {
        $ignorePatterns = config('memory-usage.ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);

        if ($event->request->is($ignorePatterns)) {
            return;
        }

        $peakUsage = $this->memoryHelper->getPeakUsage();

        foreach (config('memory-usage.paths', []) as $options) {
            $patterns = Arr::get($options, 'patterns', self::DEFAULT_PATTERNS);
            $ignorePaths = Arr::get($options, 'ignore_patterns', self::DEFAULT_IGNORE_PATTERNS);
            $limit = Arr::get($options, 'limit');

            if (! is_null($limit) && $peakUsage > $limit && $event->request->is($patterns) && ! $event->request->is($ignorePaths)) {
                $channel = Arr::get($options, 'channel', self::DEFAULT_CHANNEL);
                $level = Arr::get($options, 'level', self::DEFAULT_LEVEL);

                Log::channel($channel)->log($level, sprintf('Maximum memory %01.2f MiB used during request for %s is greater than limit of %01.2f MiB', $peakUsage, $event->request->getPathInfo(), $limit));
            }

            $environments = Arr::get($options, 'header.environments', self::DEFAULT_ENVIRONMENTS);
            $isEnabled = App::environment($environments);

            if ($event->request->is($patterns) && $isEnabled) {
                $headerName = config('memory-usage.header_name', self::DEFAULT_HEADER_NAME);

                $event->response->headers->set($headerName, $peakUsage);
            }
        }
    }
}
