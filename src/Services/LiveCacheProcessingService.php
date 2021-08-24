<?php

namespace Terraformers\KeysForCache\Services;

class LiveCacheProcessingService extends CacheProcessingService
{
    protected function shouldPublishUpdates(): bool
    {
        return true;
    }
}
