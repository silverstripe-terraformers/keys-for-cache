<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchesBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchedPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchesPage;

class DotNotationTouchesTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationTouchesTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $extra_dataobjects = [
        DotNotationTouchedPage::class,
        DotNotationTouchesPage::class,
        DotNotationTouchedBelongsToModel::class,
        DotNotationTouchedHasManyModel::class,
        DotNotationTouchedHasOneModel::class,
        DotNotationTouchesBelongsToModel::class,
    ];

    public function testTouchesHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedHasOneModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasOneModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedHasOneModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedHasOneModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedHasOneModelFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedHasOneModelSecondID, $modelTwo->ID);

        $originalKeyOne = $modelOne->getCacheKey();
        $originalKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($originalKeyOne);
        $this->assertNotNull($originalKeyTwo);
        $this->assertNotEmpty($originalKeyOne);
        $this->assertNotEmpty($originalKeyTwo);

        // Begin changes
        $page->forceChange();
        $page->write();

        $newKeyOne = $modelOne->getCacheKey();
        $newKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($newKeyOne);
        $this->assertNotNull($newKeyTwo);
        $this->assertNotEmpty($newKeyOne);
        $this->assertNotEmpty($newKeyTwo);
        $this->assertNotEquals($originalKeyOne, $newKeyOne);
        $this->assertNotEquals($originalKeyTwo, $newKeyTwo);
    }

    public function testTouchesPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedBelongsToModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedBelongsToModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedBelongsToModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedBelongsToModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedBelongsToModelFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedBelongsToModelSecondID, $modelTwo->ID);

        $originalKeyOne = $modelOne->getCacheKey();
        $originalKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($originalKeyOne);
        $this->assertNotNull($originalKeyTwo);
        $this->assertNotEmpty($originalKeyOne);
        $this->assertNotEmpty($originalKeyTwo);

        // Begin changes
        $page->forceChange();
        $page->write();

        $newKeyOne = $modelOne->getCacheKey();
        $newKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($newKeyOne);
        $this->assertNotNull($newKeyTwo);
        $this->assertNotEmpty($newKeyOne);
        $this->assertNotEmpty($newKeyTwo);
        $this->assertNotEquals($originalKeyOne, $newKeyOne);
        $this->assertNotEquals($originalKeyTwo, $newKeyTwo);
    }

    /**
     * This test is currently failing, and is a scenario we expect to support
     */
    public function testTouchesHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedHasManyModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasManyModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertCount(1, $page->TouchedHasManyModelsFirst());
        $this->assertEquals($page->TouchedHasManyModelsFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->TouchedHasManyModelsSecond());
        $this->assertEquals($page->TouchedHasManyModelsSecond()->first()->ID, $modelTwo->ID);

        $originalKeyOne = $modelOne->getCacheKey();
        $originalKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($originalKeyOne);
        $this->assertNotNull($originalKeyTwo);
        $this->assertNotEmpty($originalKeyOne);
        $this->assertNotEmpty($originalKeyTwo);

        $page->forceChange();
        $page->write();

        $newKeyOne = $modelOne->getCacheKey();
        $newKeyTwo = $modelTwo->getCacheKey();

        $this->assertNotNull($newKeyOne);
        $this->assertNotNull($newKeyTwo);
        $this->assertNotEmpty($newKeyOne);
        $this->assertNotEmpty($newKeyTwo);
        $this->assertNotEquals($originalKeyOne, $newKeyOne);
        $this->assertNotEquals($originalKeyTwo, $newKeyTwo);
    }

    public function testTouchesBelongsTo(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchedPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchesBelongsToModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchesBelongsToModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchesBelongsToModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchesBelongsToModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchesBelongsToModelFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchesBelongsToModelSecondID, $modelTwo->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $modelOne->forceChange();
        $modelOne->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);

        // Flush updates again before we trigger the next change
        ProcessedUpdatesService::singleton()->flush();

        // Begin changes
        $modelTwo->forceChange();
        $modelTwo->write();

        // Save our key again before we regenerate it
        $originalKey = $newKey;

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
