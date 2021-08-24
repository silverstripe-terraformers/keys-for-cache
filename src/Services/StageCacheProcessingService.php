<?php

namespace Terraformers\KeysForCache\Services;

class StageCacheProcessingService extends CacheProcessingService
{
    protected function publishUpdates(): bool
    {
        return false;
    }
}
