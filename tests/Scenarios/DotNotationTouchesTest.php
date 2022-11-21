<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchesBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchedPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchesPage;

class DotNotationTouchesTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationTouchesTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        DotNotationTouchedPage::class,
        DotNotationTouchesPage::class,
        DotNotationTouchedBelongsTo::class,
        DotNotationTouchedHasMany::class,
        DotNotationTouchedHasOne::class,
        DotNotationTouchesBelongsTo::class,
    ];

    public function testTouchesHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedHasOne::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasOne::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedHasOne::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedHasOne::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedHasOneFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedHasOneSecondID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationTouchedBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedBelongsTo::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedBelongsToSecondID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationTouchedHasMany::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasMany::class, 'model2');

        // Check that we're set up correctly
        $this->assertCount(1, $page->TouchedHasManyFirst());
        $this->assertEquals($page->TouchedHasManyFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->TouchedHasManySecond());
        $this->assertEquals($page->TouchedHasManySecond()->first()->ID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationTouchesBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchesBelongsTo::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchesBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchesBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchesBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchesBelongsToSecondID, $modelTwo->ID);

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
