<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

class DotNotationCaresTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationCaresTest.yml'; // phpcs:ignore

    protected static $extra_dataobjects = [
        DotNotationCaresPage::class,
        DotNotationCaredBelongsToModel::class,
        DotNotationCaredHasManyModel::class,
        DotNotationCaredHasOneModel::class,
    ];

    public function testCaresPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsToModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsToModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsToModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsToModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToModelFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToModelSecondID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsToModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsToModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsToModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsToModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToModelFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToModelSecondID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationCaredHasOneModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasOneModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredHasOneModel::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredHasOneModel::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredHasOneModelFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredHasOneModelSecondID, $modelTwo->ID);

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
        $modelOne = $this->objFromFixture(DotNotationCaredHasManyModel::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasManyModel::class, 'model2');

        // Check that we're set up correctly
        $this->assertCount(1, $page->CaredHasManyModelsFirst());
        $this->assertEquals($page->CaredHasManyModelsFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->CaredHasManyModelsSecond());
        $this->assertEquals($page->CaredHasManyModelsSecond()->first()->ID, $modelTwo->ID);

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
}
