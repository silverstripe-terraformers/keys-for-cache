<?php

namespace Terraformers\KeysForCache\Services;

class LiveCacheProcessingService extends CacheProcessingService
{
    protected function publishUpdates(): bool
    {
        return true;
    }
}
