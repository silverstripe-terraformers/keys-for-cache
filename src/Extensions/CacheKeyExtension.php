<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\DataTransferObjects\CacheKeyDto;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Services\LiveCacheProcessingService;
use Terraformers\KeysForCache\Services\StageCacheProcessingService;

/**
 * @property DataObject|$this $owner
 * @method HasManyList|CacheKey CacheKeys()
 */
class CacheKeyExtension extends DataExtension
{
    private static array $has_many = [
        // Programmatically we know that we will only ever create one of these CacheKey records per unique DataObject,
        // however, there is no unique index on CacheKey, and Silverstripe requires that our polymorphic relationships
        // be defined in this way (because a has_many will technically be possible, from a data integrety p.o.v.)
        'CacheKeys' => CacheKey::class . '.Record',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        // Field is initially a GridField (with too many options) within a Tab. We don't want that
        $fields->removeByName([
            'CacheKeys',
        ]);

        if (!$this->owner->config()->get('enable-cache-keys-field')) {
            return;
        }

        if (!$this->owner->config()->get('has_cache_key')) {
            return;
        }

        $fields->addFieldToTab(
            'Root.Settings',
            GridField::create(
                'CacheKeys',
                'Cache Keys',
                $this->owner->CacheKeys(),
                GridFieldConfig_RecordViewer::create()
            )
        );
    }

    /**
     * Return a cache key for the instance of DataObject
     *
     * @return string|null hash value of the cache key
     */
    public function getCacheKey(): ?string
    {
        $key = new CacheKeyDto($this->findCacheKeyHash());

        $this->owner->invokeWithExtensions('updateCacheKey', $key);

        return $key->getKey();
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

    /**
     * After deleting a DataObject record, ensure to
     * 1. Invalidate cache key of other Dataobject records that cares about this record
     * 2. Remove cache key of this record
     */
    public function onAfterDelete(): void
    {
        // We will want to publish changes to the CacheKey onAfterWrite if the instance triggering this event is *not*
        // Versioned (the changes should be seen immediately even though the object wasn't Published)
        $publishUpdates = !$this->owner->hasExtension(Versioned::class);
        $this->triggerEvent($publishUpdates);
        CacheKey::remove($this->owner);
    }

    /**
     * After pulishing a DataObject record, trigger the event to
     * 1. Create/update and publish the cache key for this record
     * 2. Invalidate cache keys of other Dataobject records that cares about this record
     */
    public function onAfterPublish(): void
    {
        $this->triggerEvent(true);
    }

    public function onAfterUnpublish(): void
    {
        $this->triggerEvent(true);
    }

    /**
     * Trigger the event that will create or update cache keys
     * for the current record and also for all other records that are affected by the change of this record
     *
     * @param bool $publishUpdates
     */
    protected function triggerEvent(bool $publishUpdates = false): void
    {
        $ignoreList = Config::forClass(CacheKey::class)->get('ignorelist');

        if (in_array($this->owner->ClassName, $ignoreList, true)) {
            return;
        }

        $service = $publishUpdates
            ? LiveCacheProcessingService::singleton()
            : StageCacheProcessingService::singleton();

        $service->processChange($this->owner);
    }

    /**
     * Return the KeyHash value of the CacheKey record
     *
     * @return string|null
     */
    protected function findCacheKeyHash(): ?string
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
}
