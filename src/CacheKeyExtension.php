<?php

namespace Terraformers\KeysForCache;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;

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

        $className = $this->owner->getClassName();
        $id = $this->owner->ID;

        $key = CacheKey::get()
            ->filter([
                'RecordClass' => $className,
                'RecordID' => $id,
            ])
            ->first();

        if (!$key) {
            $key = CacheKey::updateOrCreateKey($className, $id);
        }

        return $key
            ? $key->KeyHash
            : md5(microtime(false));
    }

    public function getCacheKey(): string
    {
        $key = new CacheKeyDTO($this->findCacheKeyHash());

        $this->owner->extend('updateCacheKey', $key);

        return $key->getKey();
    }

    protected function triggerEvent(): void
    {
        CacheRelationService::singleton()->processChange($this->owner);
    }

    /**
     * Events that can cause a key to change
     */

    public function onAfterWrite(): void
    {
        $this->triggerEvent();
    }

    public function onAfterPublish(): void
    {
        $this->triggerEvent();
    }

    public function onAfterUnpublish(): void
    {
        $this->triggerEvent();
    }

    public function onAfterDelete(): void
    {
        $this->triggerEvent();
        CacheKey::remove($this->owner->getClassName(), $this->owner->ID);
    }
}
