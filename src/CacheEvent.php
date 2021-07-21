<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use Symfony\Contracts\EventDispatcher\Event;
use Terraformers\KeysForCache\Models\CacheKey;

/**
 * The order.placed event is dispatched each time an order is created
 * in the system.
 */
class CacheEvent extends Event
{
    public const EVENT_FORMAT = 'event.send.%s';

    protected string $className;
    protected int $id;

    public function __construct(string $className, int $id)
    {
        $this->className = $className;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public static function handleEvent(CacheEvent $event): void
    {
        $shouldUpdateSelf = Config::get($event->getClassName())->get('care_cache');

        if ($shouldUpdateSelf) {
            CacheKey::updateOrCreateKey($event->getClassName(), $event->getId());
        }

        $cacheDependents = ConfigHelper::getConfigDependents($event->getClassName(), 'cache_dependencies');

        foreach ($cacheDependents as $dependent) {
            // Update things that worry about this
        }

        $dispatcher = static::singleton()->getDispatcher();

        $onwers = ConfigHelper::getConfigDependents($event->getClassName(), 'owns');;

        foreach ($thingsThatOwnThis as $ownThi) {
            $items = DataObject::get($ownThi)
                ->filter('Relation', $event->getId())
                ->map('ClassName', 'ID')
                ->toArray();

            foreach ($items as $className => $id) {
                $dispatcher->dispatch(
                    new CacheEvent($className, $id),
                    sprintf(CacheEvent::EVENT_FORMAT, $className)
                );
            }
        }
    }
}
