<?php

namespace Terraformers\KeysForCache\Tests\State;

use SilverStripe\Dev\SapphireTest;
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

    /**
     * Testing the default state, whereby we should be able to findOrCreate with the CacheKey being initially written
     * to the Database
     */
    public function testCanWrite(): void
    {
        $page = $this->objFromFixture(CaresPage::class, 'page1');

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

        // For this we will invoke the getCacheKey() method, as this potentially triggers a write and/or publish on
        // newly created CacheKey records
        $keyHash = $page->getCacheKey();

        // We expect KeyHash to have had a value
        $this->assertNotNull($keyHash);

        // We expect there to be one CacheKey now available on the Page
        $this->assertCount(1, $page->CacheKeys());

        $cacheKey = $page->CacheKeys()->first();

        // And we expect this to have written this CacheKey to the Database
        $this->assertTrue($cacheKey->isInDB());
        // Our page is not published, so we expect the CacheKey to also not be published
        $this->assertFalse($cacheKey->isPublished());
    }

    /**
     * Testing the state where we have determined that CacheKeys should not be written to the Database
     */
    public function testCannotWrite(): void
    {
        StagingState::disableWrite();
        StagingState::disablePublish();

        $page = $this->objFromFixture(CaresPage::class, 'page1');

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

        // For this we will first invoke the getCacheKey() method, as this potentially triggers a write and/or publish
        // on newly created CacheKey records
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
    }

    /**
     * Testing the default state, whereby we should be able to findOrCreate with the CacheKey being initially written
     * to the Database, and because our Page is published, the CacheKey should also be published
     */
    public function testCanPublish(): void
    {
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

        // For this we will first invoke the getCacheKey() method, as this potentially triggers a write and/or publish
        // on newly created CacheKey records
        $keyHash = $page->getCacheKey();

        // We expect KeyHash to have had a value
        $this->assertNotNull($keyHash);

        // We expect there to be one CacheKey now available on the Page
        $this->assertCount(1, $page->CacheKeys());

        $cacheKey = $page->CacheKeys()->first();

        // And we expect this to have written this CacheKey to the Database
        $this->assertTrue($cacheKey->isInDB());
        // Our page is not published, so we expect the CacheKey to also not be published
        $this->assertTrue($cacheKey->isPublished());
    }

    /**
     * Testing the state where we have determined that CacheKeys should not be published, even though our Page is
     * published
     */
    public function testCannotPublish(): void
    {
        StagingState::disablePublish();

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

        // For this we will first invoke the getCacheKey() method, as this potentially triggers a write and/or publish
        // on newly created CacheKey records
        $keyHash = $page->getCacheKey();

        // We expect KeyHash to have had a value
        $this->assertNotNull($keyHash);

        // We expect there to be one CacheKey now available on the Page
        $this->assertCount(1, $page->CacheKeys());

        $cacheKey = $page->CacheKeys()->first();

        // And we expect this CacheKey to be in the Database, but not be published
        $this->assertTrue($cacheKey->isInDB());
        $this->assertFalse($cacheKey->isPublished());
    }

    protected function setUp(): void
    {
        parent::setUp();

        StagingState::enableWrite();
        StagingState::enablePublish();
    }
}
