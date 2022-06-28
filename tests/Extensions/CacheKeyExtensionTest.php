<?php

namespace Terraformers\KeysForCache\Tests\Extensions;

use Page;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\GlobalCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\NoCachePage;

class CacheKeyExtensionTest extends SapphireTest
{

    protected static $fixture_file = 'CacheKeyExtensionTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        CachePage::class,
    ];

    public function testWriteGeneratesCacheKey(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // There should be exactly 1
        $this->assertCount(1, $keys);
    }

    public function testWriteDoesNotGenerateCacheKey(): void
    {
        // Page config is $has_cache_key = false, so when we write this record it should not generate a CacheKey
        $page = NoCachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // There shouldn't be any
        $this->assertCount(0, $keys);
    }

    public function testPublishUpdatesCacheKey(): void
    {
        $parent = $this->objFromFixture(CachePage::class, 'page1');
        $child = $this->objFromFixture(GlobalCaresPage::class, 'page1');

        $childKey = $child->getCacheKey();

        $this->assertNotNull($childKey);
        $this->assertNotEmpty($childKey);

        $parent->forceChange();
        $parent->publishRecursive();

        $this->assertNotEquals($childKey, $child->getCacheKey());
    }

    public function testUnpublishUpdatesCacheKey(): void
    {
        $parent = $this->objFromFixture(CachePage::class, 'page1');
        $child = $this->objFromFixture(GlobalCaresPage::class, 'page1');

        $parent->forceChange();
        $parent->publishRecursive();

        $childKey = $child->getCacheKey();

        $this->assertNotNull($childKey);
        $this->assertNotEmpty($childKey);

        ProcessedUpdatesService::singleton()->flush();

        $parent->doUnpublish();

        $this->assertNotEquals($childKey, $child->getCacheKey());
    }

    public function testDeleteUpdatesCacheKey(): void
    {
        // Flush our update service before we begin to trigger changes
        ProcessedUpdatesService::singleton()->flush();

        $parent = $this->objFromFixture(CachePage::class, 'page1');
        $child = $this->objFromFixture(GlobalCaresPage::class, 'page1');

        $childKey = $child->getCacheKey();

        $this->assertNotNull($childKey);
        $this->assertNotEmpty($childKey);

        $parent->doArchive();

        $this->assertNotEquals($childKey, $child->getCacheKey());
    }

    public function testGetCacheKey(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // There should be exactly 1
        $this->assertCount(1, $keys);

        /** @var CacheKey $key */
        $key = $keys->first();
        $pageKey = $page->getCacheKey();

        $this->assertNotNull($pageKey);
        $this->assertEquals($key->KeyHash, $pageKey);
    }

    public function testGetCacheKeyRegenerated(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Delete them, so we know we're at square one with no keys available in the DB
        foreach ($keys as $key) {
            $key->doArchive();
        }

        // Make sure we're set up correctly with no existing cache key
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        $this->assertCount(0, $keys);

        // Requesting getCacheKey should regenerate the key
        $pageKey = $page->getCacheKey();

        /** @var CacheKey $key */
        $key = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();

        $this->assertNotNull($key);

        $this->assertNotNull($pageKey);
        $this->assertNotEmpty($pageKey);

        // And check that the key itself was not published (as our page wasn't)
        $this->assertFalse($key->isPublished());
    }

    public function testGetCacheKeyRegeneratedAndPublished(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();
        $page->publishRecursive();

        // Fetch all CacheKeys for this ClassName and ID
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Delete them, so we know we're at square one with no keys available in the DB
        foreach ($keys as $key) {
            $key->doArchive();
        }

        // Make sure we're set up correctly with no existing cache key
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        $this->assertCount(0, $keys);

        // Requesting getCacheKey should regenerate the key
        $pageKey = $page->getCacheKey();

        /** @var CacheKey $key */
        $key = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ])->first();

        $this->assertNotNull($key);

        $this->assertNotNull($pageKey);
        $this->assertNotEmpty($pageKey);

        // And check that the key itself was published (as our page was)
        $this->assertTrue($key->isPublished());
    }

    public function testGetCacheKeyNoDb(): void
    {
        $page = CachePage::create();

        $this->assertNull($page->getCacheKey());
    }

    public function testGetCacheKeyNoKeyConfig(): void
    {
        $page = NoCachePage::create();
        $page->write();

        $this->assertNull($page->getCacheKey());
    }

    public function testUpdateCMSFields(): void
    {
        Page::config()->set('enable_cache_keys_field', true);

        $page = CachePage::create();
        $page->write();

        $fields = $page->getCMSFields();
        $this->assertNotNull($fields->dataFieldByName('CacheKeys'));
    }

    public function testUpdateCMSFieldsNoDisplay(): void
    {
        Page::config()->set('enable_cache_keys_field', false);

        $page = CachePage::create();
        $page->write();

        $fields = $page->getCMSFields();
        $this->assertNull($fields->dataFieldByName('CacheKeys'));
    }

    public function testUpdateCMSFieldsNoKeyConfig(): void
    {
        Page::config()->set('enable_cache_keys_field', true);

        $page = NoCachePage::create();
        $page->write();

        $fields = $page->getCMSFields();
        $this->assertNull($fields->dataFieldByName('CacheKeys'));
    }

    public function testIgnoreList(): void
    {
        // Add our CachePage to the ignorelist
        $ignoreList = CacheKey::config()->get('ignorelist');
        $ignoreList['CachePage'] = CachePage::class;
        CacheKey::config()->set('ignorelist', $ignoreList);

        // Create the Page
        $page = CachePage::create();
        $page->write();

        // No CacheKey should have been generated
        $this->assertCount(
            0,
            CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ])
        );
    }

}
