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
use Terraformers\KeysForCache\State\StagingState;

/**
 * @property DataObject|$this $owner
 * @method HasManyList|CacheKey CacheKeys()
 */
class CacheKeyExtension extends DataExtension
{
    private static array $has_many = [
        // Programmatically we know that we will only ever create one of these CacheKey records per unique DataObject,
        // however, there is no unique index on CacheKey, and Silverstripe requires that our polymorphic relationships
        // be defined in this way (because a has_many will technically be possible, from a data integrity p.o.v.)
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
        $this->owner->triggerCacheEvent($publishUpdates);
    }

    public function onAfterDelete(): void
    {
        // We will want to publish changes to the CacheKey onAfterWrite if the instance triggering this event is *not*
        // Versioned (the changes should be seen immediately even though the object wasn't Published)
        $publishUpdates = !$this->owner->hasExtension(Versioned::class);
        $this->owner->triggerCacheEvent($publishUpdates);
        CacheKey::remove($this->owner);
    }

    public function onAfterPublish(): void
    {
        $this->owner->triggerCacheEvent(true);
    }

    public function onAfterUnpublish(): void
    {
        $this->owner->triggerCacheEvent(true);
    }

    public function triggerCacheEvent(bool $publishUpdates = false): void
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

    protected function findCacheKeyHash(): ?string
    {
        // If this DataObject is not in the Database, then it cannot have a CacheKey
        if (!$this->owner->isInDB()) {
            return null;
        }

        $hasCacheKey = $this->owner->config()->get('has_cache_key');

        // You have requested that this DataObject class does not use cache keys
        if (!$hasCacheKey) {
            return null;
        }

        // Find an existing CacheKey, or create a new one for this DataObject
        $cacheKey = CacheKey::findOrCreate($this->owner);

        // In this context (that being, in a time where we are not actively generating Cache Keys, and are instead just
        // trying to find them) we will not perform a write() when/if the StagingState indicates that we should not
        if (!StagingState::singleton()->canWrite()) {
            return $cacheKey->KeyHash;
        }

        // Check that our CacheKey has been saved to the Database
        if (!$cacheKey->isInDB()) {
            $cacheKey->write();
        }

        // The Cache Key is already published, so there is nothing left for us to do except return the KeyHash
        if ($cacheKey->isPublished()) {
            return $cacheKey->KeyHash;
        }

        // In this context we will not perform a publish() when/if the StagingState indicates that we should not
        if (!StagingState::singleton()->canPublish()) {
            return $cacheKey->KeyHash;
        }

        // If the owner is not Versioned (essentially meaning that it is *always* published), or if the owner is
        // currently published, then we want to make sure we publish our CacheKey as well
        if (!$this->owner->hasExtension(Versioned::class) || $this->owner->isPublished()) {
            if (CacheKey::config()->get('publish_recursive')) {
                $cacheKey->publishRecursive();
            } else {
                $cacheKey->publishSingle();
            }
        }

        return $cacheKey->KeyHash;
    }
}
