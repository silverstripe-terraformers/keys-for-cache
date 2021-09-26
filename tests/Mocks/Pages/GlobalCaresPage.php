<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * @mixin CacheKeyExtension
 */
class GlobalCaresPage extends Page implements TestOnly
{
    private static array $global_cares = [
        'SiteConfig' => SiteConfig::class,
        'CachePage' => CachePage::class,
    ];

    private static string $table_name = 'GlobalCaresPage';

    private static bool $has_cache_key = true;
}
