<?php

namespace Terraformers\KeysForCache\Tests\Models;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\NoCachePage;

class CacheKeyTest extends SapphireTest
{
    protected static $fixture_file = 'CacheKeyTest.yml'; // phpcs:ignore

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
        CacheKey::remove($page);

        // Check we removed the record
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        $this->assertCount(0, $keys);
    }

    public function testFindOrCreateReturnsNull(): void
    {
        $page = $this->objFromFixture(NoCachePage::class, 'page1');
        // Page config is $has_cache_key = false, so when we call findOrCreate() it should return null
        $key = CacheKey::findOrCreate($page);

        $this->assertNull($key);
    }

    public function testFindOrCreateDoesFind(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Find our associated Key
        $originKey = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();
        // Check we're set up correctly
        $this->assertNotNull($originKey);

        // Keep our KeyHash around so that we can check it does not change when we findOrCreate()
        $keyHash = $originKey->KeyHash;

        // Trigger findOrCreate, which should just find
        $key = CacheKey::findOrCreate($page);

        // Check that the CacheKey exists, and that the KeyHash has not been updated
        $this->assertNotNull($key);
        $this->assertEquals($keyHash, $key->KeyHash);
    }

    public function testFindOrCreateDoesCreate(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Find our associated Key
        $originKey = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();

        // Check we found it
        $this->assertNotNull($originKey);

        // Keep our KeyHash around so that we can check it changes after we delete it and findOrCreate()
        $keyHash = $originKey->KeyHash;

        // Delete all existing Keys before we kick off
        foreach (CacheKey::get() as $cacheKey) {
            $cacheKey->doArchive();
        }

        // Make sure we're set up correctly
        $this->assertCount(0, CacheKey::get());

        $key = CacheKey::findOrCreate($page);

        // Check that the CacheKey exists, and that KeyHash is a new hash
        $this->assertNotNull($key);
        $this->assertNotEquals($keyHash, $key->KeyHash);
    }

    public function testFindOrCreateOnPublish(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Fetch any/all keys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Check that there is only 1
        $this->assertCount(1, $keys);

        // Within a LIVE reading mode, we shouldn't find any CacheKey
        Versioned::withVersionedMode(function () use ($page): void {
            Versioned::set_stage(Versioned::LIVE);

            // Fetch any/all keys for this ClassName and ID
            $keys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // Check that there are none
            $this->assertCount(0, $keys);
        });

        // Publish the Page
        $page->publishRecursive();

        // Fetch any/all keys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Check that there is still only 1
        $this->assertCount(1, $keys);

        // Within a LIVE reading mode, we should now find 1
        Versioned::withVersionedMode(function () use ($page): void {
            Versioned::set_stage(Versioned::LIVE);

            // Fetch any/all keys for this ClassName and ID
            $keys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // Check that there is 1
            $this->assertCount(1, $keys);
        });
    }

    public function testUpdateOrCreateDoesFind(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Find our associated Key
        $originKey = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();
        // Check we're set up correctly
        $this->assertNotNull($originKey);

        // Keep our KeyHash around so that we can check it does change when we updateOrCreate()
        $keyHash = $originKey->KeyHash;

        // Trigger findOrCreate, which should just find
        $key = CacheKey::updateOrCreateKey($page);

        // Check that the CacheKey exists, and that the KeyHash has been updated
        $this->assertNotNull($key);
        $this->assertNotEquals($keyHash, $key->KeyHash);
    }

    public function testUpdateOrCreateDoesCreate(): void
    {
        $page = $this->objFromFixture(CachePage::class, 'page1');
        // Find our associated Key
        $originKey = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();

        // Check we found it
        $this->assertNotNull($originKey);

        // Keep our KeyHash around so that we can check it changes after we delete it and findOrCreate()
        $keyHash = $originKey->KeyHash;

        // Delete all existing Keys before we kick off
        foreach (CacheKey::get() as $cacheKey) {
            $cacheKey->doArchive();
        }

        // Make sure we're set up correctly
        $this->assertCount(0, CacheKey::get());

        $key = CacheKey::updateOrCreateKey($page);

        // Check that the CacheKey exists, and that KeyHash is a new hash
        $this->assertNotNull($key);
        $this->assertNotEquals($keyHash, $key->KeyHash);
    }

    public function testCacheKeyPublishRecursiveDefault(): void
    {
        $this->assertFalse(CacheKey::config()->get('publish_recursive'));
    }
}
