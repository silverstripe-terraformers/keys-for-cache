<?php

namespace Terraformers\KeysForCache\Tests\Extensions;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use Page;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Models\NoCachePage;

class CacheKeyExtensionTest extends SapphireTest
{

    protected static $fixture_file = 'CacheKeyExtensionTest.yml';

    protected static $required_extensions = [
        Page::class => [
            CacheKeyExtension::class,
        ],
    ];

    public function testOnAfterWriteWithCacheKey(): void
    {
        /** @var Page|CacheKeyExtension $page */
        $page = Page::create();
        $page->Title = 'Test';
        $page->write();

        $this->assertCount(1, $page->CacheKeys());
    }

    public function testOnAfterWriteWithoutCacheKey(): void
    {
        /** @var Page|CacheKeyExtension $page */
        $page = NoCachePage::create();
        $page->Title = 'Test';
        $page->write();

        $this->assertCount(0, $page->CacheKeys());
    }

}
