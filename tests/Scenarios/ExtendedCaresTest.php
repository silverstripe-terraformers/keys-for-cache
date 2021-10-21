<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThroughModel;

class ExtendedCaresTest extends SapphireTest
{
    protected static $fixture_file = 'ExtendedCaresTest.yml'; // phpcs:ignore

    /**
     * phpcs:ignore
     */
    protected static $extra_dataobjects = [
        CaresPage::class,
        CaresPageCaredThroughModel::class,
        CaredBelongsToModel::class,
        CaredHasManyModel::class,
        CaredHasOneModel::class,
        CaredManyManyModel::class,
        CaredThroughModel::class,
        ExtendedCaresPage::class,
    ];

    public function testCaresPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsToModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsToModel::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToModelID, $model->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresBelongsTo(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsToModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsToModel::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToModelID, $model->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOneModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOneModel::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneModelID, $model->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    /**
     * This test is currently failing, and is a scenario we expect to support
     */
    public function testCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasManyModel::class, 'model1');

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $model->forceChange();
        $model->write();

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
