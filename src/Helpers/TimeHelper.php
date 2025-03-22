<?php

namespace audunru\MemoryUsage\Helpers;

class TimeHelper
{
    public function getResponseTime(): float
    {
        return microtime(true) - request()->server->get('REQUEST_TIME_FLOAT');
    }
}
