<?php

namespace Terraformers\KeysForCache\Services;

class StageCacheProcessingService extends CacheProcessingService
{
    protected function shouldPublishUpdates(): bool
    {
        return false;
    }
}
