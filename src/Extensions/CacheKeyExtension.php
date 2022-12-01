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
 * @property DataObject|Versioned|$this $owner
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

        if (!$this->owner->config()->get('enable_cache_keys_field')) {
            return;
        }

        // The configuration for this DataObject has specified that it does not use CacheKeys
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
        // a Staged Versioned DataObject (the changes should be seen immediately even though the object wasn't
        // Published)
        $publishUpdates = !$this->ownerHasStages();
        $this->owner->triggerCacheEvent($publishUpdates);
    }

    public function onAfterDelete(): void
    {
        // We will want to publish changes to the CacheKey onAfterWrite if the instance triggering this event is *not*
        // a Staged Versioned DataObject (the changes should be seen immediately even though the object wasn't
        // Published)
        $publishUpdates = !$this->ownerHasStages();
        // Note: doArchive will call deleteFromStage() which will in turn trigger this extension hook
        $this->owner->triggerCacheEvent($publishUpdates);
        CacheKey::remove($this->owner);
    }

    public function onAfterPublish(): void
    {
        $this->owner->triggerCacheEvent(true);
    }

    public function onAfterPublishRecursive(): void
    {
        // This can sometimes be called in the same request as onAfterPublish(), but the duplication of effort is
        // minimal since we keep track of which records have been processed
        $this->owner->triggerCacheEvent(true);
    }

    public function onAfterUnpublish(): void
    {
        // Note: doArchive() will call doUnpublish(), so this extension hook is called when published records are
        // archived
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

    private function findCacheKeyHash(): ?string
    {
        // If this DataObject is not in the Database, then it cannot have a CacheKey
        if (!$this->owner->isInDB()) {
            return null;
        }

        // The configuration for this DataObject has specified that it does not use CacheKeys
        if (!$this->owner->config()->get('has_cache_key')) {
            return null;
        }

        // First we'll try to find this CacheKey in whatever your current Stage is (IE: We'll fetch LIVE records when
        // you're in the LIVE reading_mode, and DRAFT records when you're in the DRAFT reading_mode)
        $cacheKey = CacheKey::findInStage($this->owner);

        // A key exists in your active Stage, so we can return it immediately
        if ($cacheKey) {
            return $cacheKey->KeyHash;
        }

        // We know that a CacheKey did not exist in your active Stage (that could have been DRAFT or LIVE). We'll now
        // attempt to find an existing CacheKey (specifically in DRAFT), or we'll create a new one

        // Given this context, our goal is now to make sure that we have a CacheKey for this record in the appropriate
        // Stage. EG: If you are browsing this record in LIVE, then we'd expect to have a published CacheKey
        $cacheKey = CacheKey::findOrCreate($this->owner);

        // Safety first, but there shouldn't really have been a reason for this to be null
        if (!$cacheKey) {
            return null;
        }

        // In this context (that being, in a time when we are not actively generating Cache Keys, and are instead just
        // trying to find them) we will not write() when/if the StagingState indicates that we should not
        // One example is when browsing CMS Previews. We do not save CacheKeys in that context
        if (!StagingState::singleton()->canWrite()) {
            return $cacheKey->KeyHash;
        }

        // Make sure that our CacheKey is saved to the Database
        if (!$cacheKey->isInDB()) {
            // We need to make sure that we are specifically writing this with reading mode set to DRAFT. If we write()
            // while a user is browsing in a LIVE reading mode, then this CacheKey will be "live" immediately
            // @see https://github.com/silverstripe/silverstripe-versioned/issues/382
            Versioned::withVersionedMode(static function () use ($cacheKey): void {
                Versioned::set_stage(Versioned::DRAFT);

                $cacheKey->write();
            });
        }

        // In this context we will not publish() when/if the StagingState indicates that we should not
        // Generally, any time we're in a DRAFT reading_mode, we will not publish
        if (!StagingState::singleton()->canPublish()) {
            return $cacheKey->KeyHash;
        }

        // The Cache Key is already published, so there is nothing left for us to do except return the KeyHash
        if ($cacheKey->isPublished()) {
            return $cacheKey->KeyHash;
        }

        // Default behaviour is that publish_recursive is disabled. There is only value in using publishRecursive()
        // if you decide that your CacheKey model needs to $own something
        if (CacheKey::config()->get('publish_recursive')) {
            $cacheKey->publishRecursive();
        } else {
            $cacheKey->publishSingle();
        }

        return $cacheKey->KeyHash;
    }

    private function ownerHasStages(): bool
    {
        // This DataObject does not have the Versioned extension, so it definitely doesn't have stages (IE: Draft and
        // Live versions)
        if (!$this->owner->hasExtension(Versioned::class)) {
            return false;
        }

        // The Versioned extensions has two modes. The one that we're (probably) all familiar with, where we have a
        // Draft and Live version, but there is also a mode that does not have stages, and instead only has _Versions.
        // If this DataObject does not have stages, then we're going to want to treat this the same as a non-Versioned
        // DataObject
        return $this->owner->hasStages();
    }
}
