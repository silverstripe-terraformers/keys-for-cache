<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchesBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchedPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\TouchesPageTouchedThroughModel;

class TouchesTest extends SapphireTest
{
    protected static $fixture_file = 'TouchesTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        PolymorphicTouchedHasOneModel::class,
        PolymorphicTouchedHasManyModel::class,
        TouchedPage::class,
        TouchesPage::class,
        TouchesPageTouchedThroughModel::class,
        TouchedBelongsToModel::class,
        TouchedHasManyModel::class,
        TouchedHasOneModel::class,
        TouchedManyManyModel::class,
        TouchedThroughModel::class,
        TouchesBelongsToModel::class,
    ];

    public function testTouchesHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedHasOneModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(TouchedHasOneModel::class, $model->ClassName);
        $this->assertEquals($page->TouchedHasOneModelID, $model->ID);

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testTouchesTrueHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedBelongsToModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(TouchedBelongsToModel::class, $model->ClassName);
        $this->assertEquals($page->TouchedBelongsToModelID, $model->ID);

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testPolymorphicTouchesHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicTouchedHasOneModel::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(PolymorphicTouchedHasOneModel::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testTouchesHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedHasManyModel::class, 'model1');

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testPolymorphicTouchesHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicTouchedHasManyModel::class, 'model1');

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testTouchesManyMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedManyManyModel::class, 'model1');

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedManyManyModels());
        $this->assertEquals($model->ID, $page->TouchedManyManyModels()->first()->ID);

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin triggering changes
        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testTouchesThrough(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedThroughModel::class, 'model1');

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedThroughModels());
        $this->assertEquals($model->ID, $page->TouchedThroughModels()->first()->ID);

        $originalKey = $model->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin triggering changes
        $page->forceChange();
        $page->write();

        $newKey = $model->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($originalKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testTouchesBelongsTo(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchedPage::class, 'page1');
        $model = $this->objFromFixture(TouchesBelongsToModel::class, 'model1');

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
