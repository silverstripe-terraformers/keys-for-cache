<?php

namespace Terraformers\KeysForCache\State;

use SilverStripe\Core\Injector\Injectable;

class StagingState
{

    use Injectable;

    public bool $enableRead = true;

    public bool $enablePublish = true;

    public static function disableRead(): void
    {
        static::singleton()->enableRead = false;
    }

    public static function canRead(): bool
    {
        return static::singleton()->enableRead;
    }

    public static function disablePublish(): void
    {
        static::singleton()->enablePublish = false;
    }

    public static function canPublish(): string
    {
        return static::singleton()->enablePublish;
    }

}
