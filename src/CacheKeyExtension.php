<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\Config\Config;
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

        return $key->KeyHash;
    }

    public function getCacheKey(): string
    {
        $key = new CacheKeyDTO($this->findCacheKeyHash());

        $this->owner->extend('updateCacheKey', $key);

        return $key->getKey();
    }

    protected function triggerEvent(): void
    {
        // Update the items cache key if required
        if (Config::forClass(get_class($this->owner))->get('has_cache_key')) {
            CacheKey::updateOrCreateKey($this->owner->getClassName(), $this->owner->ID);
        }

        return;
        foreach ($this->owner->config()->get('trigger_blacklist') as $blacklistClassName) {
            if (is_a($this->owner, $blacklistClassName)) {
                return;
            }
        }

        EventManager::singleton()->handleCacheEvent($this->owner->ClassName, $this->owner->ID);
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
        // TODO: REMOVE CACHE KEY
        $this->triggerEvent();
    }
}
