<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

class DotNotationCaresTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationCaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        DotNotationCaresPage::class,
        DotNotationCaredBelongsTo::class,
        DotNotationCaredHasMany::class,
        DotNotationCaredHasOne::class,
    ];

    public function testCaresPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToSecondID, $modelTwo->ID);

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

        // Flush updates again before we trigger another update
        ProcessedUpdatesService::singleton()->flush();

        // Update original key for the next change
        $originalKey = $newKey;

        // Begin changes
        $modelTwo->forceChange();
        $modelTwo->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresBelongsTo(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToSecondID, $modelTwo->ID);

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

    public function testCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredHasOne::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasOne::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredHasOne::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredHasOne::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredHasOneFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredHasOneSecondID, $modelTwo->ID);

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

        // Flush updates again before we trigger another update
        ProcessedUpdatesService::singleton()->flush();

        // Update original key for the next change
        $originalKey = $newKey;

        // Begin changes
        $modelTwo->forceChange();
        $modelTwo->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredHasMany::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasMany::class, 'model2');

        // Check that we're set up correctly
        $this->assertCount(1, $page->CaredHasManyFirst());
        $this->assertEquals($page->CaredHasManyFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->CaredHasManySecond());
        $this->assertEquals($page->CaredHasManySecond()->first()->ID, $modelTwo->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $modelOne->forceChange();
        $modelOne->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);

        // Flush updates again before we trigger another update
        ProcessedUpdatesService::singleton()->flush();

        // Update original key for the next change
        $originalKey = $newKey;

        $modelTwo->forceChange();
        $modelTwo->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
