<?php

namespace Terraformers\KeysForCache\Events;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use Symfony\Contracts\EventDispatcher\Event;
use Terraformers\KeysForCache\Models\CacheKey;

class DependencyEvent extends Event
{
    protected string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
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

}
