<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\DataTransferObjects\CacheKeyDto;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Services\LiveCacheProcessingService;
use Terraformers\KeysForCache\Services\StageCacheProcessingService;

/**
 * @property DataObject|$this $owner
 */
class CacheKeyExtension extends DataExtension
{
    public function findCacheKeyHash(): ?string
    {
        if (!$this->owner->isInDB()) {
            return null;
        }

        $hasCacheKey = $this->owner->config()->get('has_cache_key');

        if (!$hasCacheKey) {
            return null;
        }

        // Update or create (in this case, it will be create)
        $cacheKey = CacheKey::findOrCreate($this->owner);

        if (!$cacheKey->isPublished()) {
            // If the owner is not Versioned, or if it has been published, then we want to make sure we publish our
            // CacheKey at the same time
            if (!$this->owner->hasExtension(Versioned::class) || $this->owner->isPublished()) {
                $cacheKey->publishRecursive();
            }
        }

        return $cacheKey->KeyHash;
    }

    public function getCacheKey(): ?string
    {
        $key = new CacheKeyDto($this->findCacheKeyHash());

        $this->owner->invokeWithExtensions('updateCacheKey', $key);

        return $key->getKey();
    }

    protected function triggerEvent(bool $publishUpdates = false): void
    {
        $blacklist = Config::forClass(CacheKey::class)->get('blacklist');

        if (in_array($this->owner->ClassName, $blacklist)) {
            return;
        }

        $service = $publishUpdates
            ? LiveCacheProcessingService::singleton()
            : StageCacheProcessingService::singleton();

        $service->processChange($this->owner);
    }

    /**
     * Events that can cause a key to change
     */

    public function onAfterWrite(): void
    {
        // We will want to publish changes to the CacheKey onAfterWrite if the instance triggering this event is *not*
        // Versioned (the changes should be seen immediately even though the object wasn't Published)
        $publishUpdates = !$this->owner->hasExtension(Versioned::class);
        $this->triggerEvent($publishUpdates);
    }

    public function onAfterDelete(): void
    {
        // We will want to publish changes to the CacheKey onAfterWrite if the instance triggering this event is *not*
        // Versioned (the changes should be seen immediately even though the object wasn't Published)
        $publishUpdates = !$this->owner->hasExtension(Versioned::class);
        $this->triggerEvent();
        CacheKey::remove($this->owner->getClassName(), $this->owner->ID);
    }

    public function onAfterPublish(): void
    {
        $this->triggerEvent(true);
    }

    public function onAfterUnpublish(): void
    {
        $this->triggerEvent(true);
    }
}
