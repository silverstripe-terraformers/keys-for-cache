<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Terraformers\KeysForCache\Models\CacheKey;

class EventManager
{
    use Injectable;

    private ?EventDispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
        self::boot();
    }


    public static function handleEvent(DataObject $record): void
    {
        CacheKey::updateOrCreateKey($record);

        $dispatcher = static::singleton()->getDispatcher();
        $eventName = sprintf(CacheEvent::EVENT_NAME, $record->ClassName);
        $dispatcher->dispatch(new CacheEvent($record->ClassName, $record->ID), $eventName);
    }

    protected static function findSubscriptions(DataObject $record): void
    {

    }

    public function getDispatcher(): ?EventDispatcher
    {
        return $this->dispatcher;
    }

    // Boots up the subscribersd
    public function boot(): void
    {
        $dispatcher = static::singleton()->getDispatcher();
        $eventName = sprintf(CacheEvent::EVENT_NAME, $item);
        $dispatcher->addListener($eventName, function(CacheEvent $event) {
            CacheEvent::handleEvent($event);
        });
    }
}
