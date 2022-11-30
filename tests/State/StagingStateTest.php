<?php

namespace Terraformers\KeysForCache\Tests\State;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\State\StagingState;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

class StagingStateTest extends SapphireTest
{
    protected static $fixture_file = 'StagingStateTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        CaresPage::class,
    ];

    public function testReadingMode(): void
    {
        // Default state is both enabled
        StagingState::singleton()->enableWrite();
        StagingState::singleton()->enablePublish();

        Versioned::withVersionedMode(function (): void {
            Versioned::set_stage(Versioned::LIVE);

            // In LIVE reading mode we expect the default to be that we can write and publish
            $this->assertTrue(StagingState::singleton()->canWrite());
            $this->assertTrue(StagingState::singleton()->canPublish());
        });

        Versioned::withVersionedMode(function (): void {
            Versioned::set_stage(Versioned::DRAFT);

            // In DRAFT reading mode we expect the default to be that we can write but not publish
            $this->assertTrue(StagingState::singleton()->canWrite());
            $this->assertFalse(StagingState::singleton()->canPublish());
        });

        Versioned::withVersionedMode(function (): void {
            Versioned::set_stage(Versioned::LIVE);

            // Now specifically setting our State to disable write and publish even in LIVE reading mode
            StagingState::singleton()->disableWrite();
            StagingState::singleton()->disablePublish();

            // In DRAFT reading mode we expect the default to be that we can write but not publish
            $this->assertFalse(StagingState::singleton()->canWrite());
            $this->assertFalse(StagingState::singleton()->canPublish());
        });
    }

    /**
     * Testing the state where we have determined that CacheKeys should not be written to the Database
     */
    public function testCannotWrite(): void
    {
        StagingState::singleton()->disableWrite();
        StagingState::singleton()->disablePublish();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        // Publish our Page
        $page->publishRecursive();

        // Need to remove any existing keys before we get started
        CacheKey::remove($page);

        // Check we're set up correctly
        $this->assertCount(
            0,
            CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ])
        );

        Versioned::withVersionedMode(function () use ($page): void {
            // Specifically fetching with LIVE reading mode so that this is not a limiting factor for whether we are
            // able to write CacheKeys
            Versioned::set_stage(Versioned::LIVE);

            // For this we will first invoke the getCacheKey() method, as this potentially triggers write() and/or
            // publishSingle() on newly created CacheKey records
            $keyHash = $page->getCacheKey();

            // We expect KeyHash to have had a value
            $this->assertNotNull($keyHash);

            // But we expect there to still be no CacheKeys for this DataObject
            $this->assertCount(
                0,
                CacheKey::get()->filter([
                    'RecordClass' => $page->ClassName,
                    'RecordID' => $page->ID,
                ])
            );
        });
    }

    /**
     * Testing the state where we have determined that CacheKeys should not be published, even though our Page is
     * published
     */
    public function testCannotPublish(): void
    {
        StagingState::singleton()->enableWrite();
        StagingState::singleton()->disablePublish();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $page->publishRecursive();

        // Need to remove any existing keys before we get started
        CacheKey::remove($page);

        // Check we're set up correctly
        $this->assertCount(
            0,
            CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ])
        );

        Versioned::withVersionedMode(function () use ($page): void {
            // Specifically fetching with LIVE reading mode so that this is not a limiting factor for whether we are
            // able to write and publish CacheKeys
            Versioned::set_stage(Versioned::LIVE);

            // For this we will first invoke the getCacheKey() method, as this potentially triggers write() and/or
            // publishSingle() on newly created CacheKey records
            // Because publishing is disabled, we would expect to receive a value back here, but we are expecting that
            // CacheKey to *not* be published
            $keyHash = $page->getCacheKey();

            // We expect KeyHash to have had a value
            $this->assertNotNull($keyHash);
            $this->assertNotEmpty($keyHash);

            // Re-fetch CacheKeys for this page
            /** @var DataList|CacheKey[] $keys */
            $keys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // We're currently in a LIVE reading mode, so we'd expect to *not* find this CacheKey (as it was not
            // published)
            $this->assertCount(0, $keys);
        });

        Versioned::withVersionedMode(function () use ($page): void {
            // We'll now switch to a DRAFT reading mode and check that we can find the CacheKey
            Versioned::set_stage(Versioned::DRAFT);

            // Fetch CacheKeys for this page
            /** @var DataList|CacheKey[] $keys */
            $keys = CacheKey::get()->filter([
                'RecordClass' => $page->ClassName,
                'RecordID' => $page->ID,
            ]);

            // We expect to find the CacheKey now that we're in a DRAFT reading mode
            $this->assertCount(1, $keys);

            /** @var CacheKey $cacheKey */
            $cacheKey = $keys->first();

            // But we expect the CacheKey to *not* be published
            $this->assertTrue($cacheKey->isInDB());
            $this->assertFalse($cacheKey->isPublished());
        });
    }
}
