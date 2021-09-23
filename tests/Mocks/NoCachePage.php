<?php

namespace Terraformers\KeysForCache\Tests\Mocks;

use SilverStripe\Dev\TestOnly;
use Page;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * @mixin CacheKeyExtension
 */
class NoCachePage extends Page implements TestOnly
{
    private static bool $has_cache_key = false;
}
