<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * @mixin CacheKeyExtension
 */
class NoCachePage extends Page implements TestOnly
{
    private static string $table_name = 'NoCachePage';

    private static bool $has_cache_key = false;
}
