<?php

namespace Terraformers\KeysForCache\Tests\Models;

use Page;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Tests\Mocks\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\NoCachePage;

class CacheKeyTest extends SapphireTest
{
    protected static $fixture_file = 'CacheKeyTest.yml';

    public function testRemove(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Check we're set up correctly
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        $this->assertCount(1, $keys);

        // Trigger archive
        CacheKey::remove($page->ClassName, $page->ID);

        // Check we removed the record
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        $this->assertCount(0, $keys);
    }

    public function testFindOrCreateReturnsNull(): void
    {
        // Page config is $has_cache_key = false, so when we call findOrCreate() it should return null
        $key = CacheKey::findOrCreate(NoCachePage::class, 1);

        $this->assertNull($key);
    }

    public function testFindOrCreateDoesFind(): void
    {
        $key = $this->objFromFixture(CacheKey::class, 'key1');
        // Check we're set up correctly
        $this->assertNotNull($key);

        // Keep our KeyHash around so that we can check it changes
        $keyHash = $key->KeyHash;

        // Trigger findOrCreate, which should just find
        $key = CacheKey::findOrCreate(Page::class, 999);

        // Check that the CacheKey exists, and that the KeyHash has not been updated
        $this->assertNotNull($key);
        $this->assertEquals($keyHash, $key->KeyHash);
    }

    public function testFindOrCreateDoesCreate(): void
    {
        $key = CacheKey::findOrCreate(Page::class, 998);

        // Check that the CacheKey exists, and that KeyHash is a new hash
        $this->assertNotNull($key);
        $this->assertNotEmpty($key->KeyHash);
    }

    public function testUpdateOrCreateDoesFind(): void
    {
        $key = $this->objFromFixture(CacheKey::class, 'key1');
        // Check we're set up correctly
        $this->assertNotNull($key);
        $this->assertNotEmpty($key->KeyHash);

        // Keep our KeyHash around so that we can check it changes
        $keyHash = $key->KeyHash;

        // Trigger updateOrCreate (we should update)
        $key = CacheKey::updateOrCreateKey(Page::class, 999);

        // Check that the CacheKey exists, and that the KeyHash has been updated
        $this->assertNotNull($key);
        $this->assertNotEquals($keyHash, $key->KeyHash);
    }

    public function testUpdateOrCreateDoesCreate(): void
    {
        // Trigger updateOrCreate (we should create)
        $key = CacheKey::updateOrCreateKey(Page::class, 997);

        // Check that the CacheKey exists, and that the KeyHash has been updated
        $this->assertNotNull($key);
        $this->assertNotEmpty($key->KeyHash);
    }
}
