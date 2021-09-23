<?php

namespace Terraformers\KeysForCache\Tests\Mocks;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use Page;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * @mixin CacheKeyExtension
 */
class CaresPage extends Page implements TestOnly
{
    private static bool $has_cache_key = true;

    private static array $cares = [
        'Parent' => SiteTree::class,
    ];
}
