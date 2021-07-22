<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\Versioned\ChangeSet;
use SilverStripe\Versioned\ChangeSetItem;
use Terraformers\KeysForCache\EventManager;
use Terraformers\KeysForCache\Models\CacheKey;

/**
 * @property DataObject|$this $owner
 * @method HasManyList|CacheKey[] CacheKeys()
 */
class CacheKeyExtension extends DataExtension
{

    private static array $trigger_blacklist = [
        CacheKey::class,
        ChangeSet::class,
        ChangeSetItem::class,
        LoginSession::class,
    ];

    private static $has_many = [
        'CacheKeys' => CacheKey::class . '.Record',
    ];

    private static $owns = [
        'CacheKeys',
    ];

    private static $cascade_deletes = [
        'CacheKeys',
    ];

    private static $cascade_duplicates = [
        'CacheKeys',
    ];

    public function getCacheKey(): string
    {
        return implode(
            '-',
            array_keys($this->owner->CacheKeys()->map('KeyHash')->toArray())
        );
    }

    public function onAfterWrite()
    {
        $this->triggerEvent();
    }

    public function onAfterPublish()
    {
        $this->triggerEvent();
    }

    public function onAfterUnpublish()
    {
        $this->triggerEvent();
    }

    public function onAfterDelete()
    {
        $this->triggerEvent();
    }

    protected function triggerEvent(): void
    {
        foreach ($this->owner->config()->get('trigger_blacklist') as $blacklistClassName) {
            if (is_a($this->owner, $blacklistClassName)) {
                return;
            }
        }

        EventManager::singleton()->handleCacheEvent($this->owner->ClassName, $this->owner->ID);
    }

}
