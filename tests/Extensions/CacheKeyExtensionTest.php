<?php

namespace Terraformers\KeysForCache\Tests;

use App\Models\MenuItem;
use Page;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use Terraformers\KeysForCache\EventManager;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Models\CacheKey;
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

    public function testGiveThisAName(): void
    {
        Debug::dump('-----------------------------');
        Debug::dump('-----------------------------');
        Debug::dump('-----------------------------');
        Debug::dump('-----------------------------');
        Debug::dump('-----------------------------');
        foreach (CacheKey::get() as $cacheKey) {
            $cacheKey->delete();
        }

        EventManager::singleton()->flushCache();
        $item = MenuItem::create();
        $item->Title = 'Item 1';
        $item->write();

        /** @var DataList|CacheKey[] $cacheKeys */
        $cacheKeys = CacheKey::get();
        Debug::dump($cacheKeys->count());

        foreach ($cacheKeys as $cacheKey) {
            Debug::dump($cacheKey->RecordClass . ' ' . $cacheKey->RecordID);
        }
    }

}
