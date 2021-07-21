<?php

namespace Terraformers;

use SilverStripe\ORM\DataObject;
use Symfony\Contracts\EventDispatcher\Event;
use Terraformers\KeysForCache\Models\CacheKey;

/**
 * The order.placed event is dispatched each time an order is created
 * in the system.
 */
class CacheEvent extends Event
{
    private const EVENT_NAME = 'event.send.%s';

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
        $shouldUpdateSelf = Config::get($item)->get('i_care_cache');

        if ($shouldUpdateSelf) {
            CacheKey::updateOrCreateKey($event->getClassName(), $event->getId());
        }

        $worries = GeneralWorries::getForClass($item);
        foreach ($worries as $worry) {
            // Update things that worry about this
        }

        $dispatcher = static::singleton()->getDispatcher();
        /** @var array $thingsThatOwnThis */
        $thingsThatOwnThis = GetThem();

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
