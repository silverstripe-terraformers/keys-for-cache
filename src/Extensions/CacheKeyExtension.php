<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\DataTransferObjects\CacheKeyDTO;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Services\CacheRelationService;
use Terraformers\KeysForCache\Services\StageCacheRelationService;
use Terraformers\KeysForCache\Services\LiveCacheRelationService;

/**
 * @property DataObject|$this $owner
 */
class CacheKeyExtension extends DataExtension
{
    public function findCacheKeyHash(): string
    {
        if (!$this->owner->isInDB()) {
            return md5(microtime(false));
        }

        $className = $this->owner->ClassName;
        $id = $this->owner->ID;
        $hasCacheKey = Config::forClass($className)->get('has_cache_key');

        if (!$hasCacheKey) {
            return md5(microtime(false));
        }

        $cacheKey = CacheKey::get()
            ->filter([
                'RecordClass' => $className,
                'RecordID' => $id,
            ])
            ->first();

        // No CacheKey exists for this record, but it should. It's possible that it was cleared during a global_cares
        // purge, or perhaps the module was added after Models existed in the DB
        if (!$cacheKey) {
            // Update or create (in this case, it will be create)
            $cacheKey = CacheKey::updateOrCreateKey($className, $id);
            $cacheKey->write();

            // If the owner is not Versioned, or if it has been published, then we want to make sure we publish our
            // CacheKey at the same time
            if (!$this->owner->hasExtension(Versioned::class) || $this->owner->isPublished()) {
                $cacheKey->publishRecursive();
            }
        }

        return $cacheKey;
    }

    public function getCacheKey(): string
    {
        $key = new CacheKeyDTO($this->findCacheKeyHash());

        $this->owner->extend('updateCacheKey', $key);

        return $key->getKey();
    }

    protected function triggerEvent(bool $publishUpdates = false): void
    {
        $blacklist = Config::forClass(CacheKey::class)->get('blacklist');

        if (in_array($this->owner->ClassName, $blacklist)) {
            return;
        }

        $service = $publishUpdates
            ? LiveCacheRelationService::singleton()
            : StageCacheRelationService::singleton();

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