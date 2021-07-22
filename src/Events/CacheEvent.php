<?php

namespace Terraformers\KeysForCache\Events;

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
}
