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
class TouchesPage extends Page implements TestOnly
{
    private static bool $has_cache_key = false;

    private static array $touches = [
        'SiteConfig' => SiteConfig::class,
        'Parent' => SiteTree::class,
    ];
}
