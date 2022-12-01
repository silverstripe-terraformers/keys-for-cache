<?php

namespace Terraformers\KeysForCache\Tests\Extensions;

use Page;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CachePage;
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

    /**
     * @dataProvider readingModes
     */
    public function testWriteGeneratesCacheKey(string $readingMode): void
    {
        $page = Versioned::withVersionedMode(static function () use ($readingMode): CachePage {
            // Testing within our requested reading mode
            Versioned::set_stage($readingMode);

            // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
            $page = CachePage::create();
            // We are performing this test across both reading modes, but we expect CacheKeys to respect the action,
            // rather than the reading mode (that being, write() creates DRAFT, and publish() creates LIVE)
            $page->write();

            return $page;
        });

        Versioned::withVersionedMode(function () use ($page): void {
            // Always fetch in DRAFT stage (because we expect this CacheKey to only be available in DRAFT)
            Versioned::set_stage(Versioned::DRAFT);

            // Re-fetch all CacheKeys for this ClassName and ID
            $cacheKeys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // There should be exactly 1
            $this->assertCount(1, $cacheKeys);

            /** @var CacheKey $cacheKey */
            $cacheKey = $cacheKeys->first();

            // Performing write() on our Page should mean that the CacheKey is not published, regardless of what
            // reading mode we were in
            $this->assertFalse($cacheKey->isPublished());
        });
    }

    /**
     * @dataProvider readingModes
     */
    public function testWriteDoesNotGenerateCacheKey(string $readingMode): void
    {
        Versioned::withVersionedMode(function () use ($readingMode): void {
            // Testing within our requested reading mode
            Versioned::set_stage($readingMode);

            // Page config is $has_cache_key = false, so when we write this record it should not generate a CacheKey
            $page = NoCachePage::create();
            $page->write();

            // We expect getCacheKey() to always return null
            $this->assertNull($page->getCacheKey());

            // Re-fetch all CacheKeys for this ClassName and ID
            $cacheKeys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // There shouldn't be any CacheKeys
            $this->assertCount(0, $cacheKeys);
        });
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

    public function testGetCacheKeyRegeneratedDraftOnly(): void
    {
        // For this test we need a published Page with no CacheKey
        /** @var CachePage $page */
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

        // Now that we're set up for the test, we want to attempt to find the CacheKeyHash for this page while we're in
        // a DRAFT reading mode
        $keyHash = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with DRAFT reading mode
            Versioned::set_stage(Versioned::DRAFT);

            // Expected behaviour here is that we should have created a brand new CacheKey for this Page, but we won't
            // publish it because we're not in a LIVE reading mode
            return $page->getCacheKey();
        });

        $this->assertNotNull($keyHash);
        $this->assertNotEmpty($keyHash);

        // Re-fetch CacheKeys for this page
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // We expect there to only be one
        $this->assertCount(1, $keys);

        /** @var CacheKey $key */
        $key = $keys->first();

        // We expect that the CacheKey was not published
        $this->assertFalse($key->isPublished());
    }

    public function testGetCacheKeyRegeneratedAndPublished(): void
    {
        // For this test we need a published Page with no CacheKey
        /** @var CachePage $page */
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

        // Now that we're set up for the test, we want to attempt to find the CacheKeyHash for this page while we're in
        // a LIVE reading mode
        $keyHash = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with LIVE reading mode
            Versioned::set_stage(Versioned::LIVE);

            // Expected behaviour here is that we should have created a brand new CacheKey for this Page, and we should
            // publish it because we're in a LIVE reading mode
            return $page->getCacheKey();
        });

        $this->assertNotNull($keyHash);
        $this->assertNotEmpty($keyHash);

        // Re-fetch CacheKeys for this page
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // We expect there to only be one
        $this->assertCount(1, $keys);

        /** @var CacheKey $key */
        $key = $keys->first();

        // We expect that one CacheKey to have been published
        $this->assertTrue($key->isPublished());
    }

    public function testGetCacheKeyPublishedFromDraft(): void
    {
        // For this test we need a published Page with no CacheKey
        /** @var CachePage $page */
        $page = CachePage::create();
        $page->write();
        $page->publishRecursive();

        // Fetch all CacheKeys for this ClassName and ID
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Make sure there is only one CacheKey
        $this->assertCount(1, $keys);

        /** @var CacheKey $key */
        $key = $keys->first();
        // Save away our KeyHash so that we can check it doesn't change
        $keyHashOriginal = $key->KeyHash;

        // Unpublish that one CacheKey
        $key->doUnpublish();

        // Re-fetch our key just to make sure that it is unpublished
        $key = CacheKey::get()->byID($key->ID);

        $this->assertNotNull($key);
        $this->assertFalse($key->isPublished());

        // Now that we're set up for the test, we want to attempt to find the CacheKeyHash for this page while we're in
        // a LIVE reading mode
        $keyHashNew = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with LIVE reading mode
            Versioned::set_stage(Versioned::LIVE);

            // Expected behaviour here is that we should find the original CacheKey (DRAFT only) and publish it
            return $page->getCacheKey();
        });

        $this->assertNotNull($keyHashNew);
        // This is the same CacheKey record as before, so this shouldn't have changed
        $this->assertSame($keyHashOriginal, $keyHashNew);

        // Re-fetch CacheKeys for this page
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // We expect there to only be one
        $this->assertCount(1, $keys);

        /** @var CacheKey $key */
        $key = $keys->first();

        // We expect that one CacheKey to have been published
        $this->assertTrue($key->isPublished());
    }

    public function testCacheKeyStagesDiffer(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();
        $page->publishRecursive();

        // Fetch all CacheKeys for this ClassName and ID to make sure we're set up correctly
        /** @var DataList|CacheKey[] $keys */
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // Make sure we have exactly one
        $this->assertCount(1, $keys);

        // Ok, so we've confirmed that we only have 1 CacheKey for our Page, now we want to fetch that CacheKey in
        // specific Stages (DRAFT vs LIVE)

        // Here's our CacheKey in DRAFT
        $draftOriginal = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with DRAFT reading mode
            Versioned::set_stage(Versioned::DRAFT);

            $cacheKey = CacheKey::get()
                ->filter([
                    'RecordClass' => $page->ClassName,
                    'RecordID' => $page->ID,
                ])
                ->first();

            if (!$cacheKey) {
                return null;
            }

            return $cacheKey->KeyHash;
        });

        // Here's our CacheKey in LIVE
        $liveOriginal = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with DRAFT reading mode
            Versioned::set_stage(Versioned::LIVE);

            $cacheKey = CacheKey::get()
                ->filter([
                    'RecordClass' => $page->ClassName,
                    'RecordID' => $page->ID,
                ])
                ->first();

            if (!$cacheKey) {
                return null;
            }

            return $cacheKey->KeyHash;
        });

        // Make sure they both existed
        $this->assertNotNull($draftOriginal);
        $this->assertNotNull($liveOriginal);
        // These should be exactly the same, because we've recently published our Page
        $this->assertEquals($draftOriginal, $liveOriginal);

        // Need to flush our service so that keys are updated with future changes
        ProcessedUpdatesService::singleton()->flush();

        // Cool. So now let's make a DRAFT only change on our Page
        $page->forceChange();
        $page->write();

        // Re-fetch our CacheKeys. This time we'll expect them to be different

        // Here's our CacheKey in DRAFT
        /** @var CacheKey $draftNew */
        $draftNew = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with DRAFT reading mode
            Versioned::set_stage(Versioned::DRAFT);

            $cacheKey = CacheKey::get()
                ->filter([
                    'RecordClass' => $page->ClassName,
                    'RecordID' => $page->ID,
                ])
                ->first();

            if (!$cacheKey) {
                return null;
            }

            return $cacheKey->KeyHash;
        });

        // Here's our CacheKey in LIVE
        /** @var CacheKey $liveNew */
        $liveNew = Versioned::withVersionedMode(static function () use ($page): ?string {
            // Specifically fetching with DRAFT reading mode
            Versioned::set_stage(Versioned::LIVE);

            $cacheKey = CacheKey::get()
                ->filter([
                    'RecordClass' => $page->ClassName,
                    'RecordID' => $page->ID,
                ])
                ->first();

            if (!$cacheKey) {
                return null;
            }

            return $cacheKey->KeyHash;
        });

        // Make sure they both existed
        $this->assertNotNull($draftNew);
        $this->assertNotNull($liveNew);
        // We would expect the new DRAFT to differ from the original DRAFT
        $this->assertNotEquals($draftOriginal, $draftNew);
        // We would expect the new DRAFT and new LIVE to differ
        $this->assertNotEquals($draftNew, $liveNew);
        // We would expect the new LIVE to be the same as the original LIVE
        $this->assertEquals($liveOriginal, $liveNew);
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

    public function readingModes(): array
    {
        return [
            [Versioned::DRAFT],
            [Versioned::LIVE],
        ];
    }

}
