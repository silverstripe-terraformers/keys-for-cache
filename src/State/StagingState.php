<?php

namespace Terraformers\KeysForCache\State;

use SilverStripe\Core\Injector\Injectable;

class StagingState
{

    use Injectable;

    public bool $enableWrite = true;

    public bool $enablePublish = true;

    public static function enableWrite(): void
    {
        static::singleton()->enableWrite = true;
    }

    public static function disableWrite(): void
    {
        static::singleton()->enableWrite = false;
    }

    public static function canWrite(): bool
    {
        return static::singleton()->enableWrite;
    }

    public static function enablePublish(): void
    {
        static::singleton()->enablePublish = true;
    }

    public static function disablePublish(): void
    {
        static::singleton()->enablePublish = false;
    }

    public static function canPublish(): bool
    {
        return static::singleton()->enablePublish;
    }

}
