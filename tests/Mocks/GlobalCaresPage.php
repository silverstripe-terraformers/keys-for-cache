<?php

namespace Terraformers\KeysForCache\Tests\Mocks;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use Page;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * @mixin CacheKeyExtension
 */
class GlobalCaresPage extends Page implements TestOnly
{
    private static bool $has_cache_key = true;

    private static array $global_cares = [
        'SiteTree' => SiteTree::class,
        'SiteConfig' => SiteConfig::class,
    ];
}
