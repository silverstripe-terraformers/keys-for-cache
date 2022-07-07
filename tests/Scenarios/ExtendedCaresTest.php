<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedCaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThroughModel;

class ExtendedCaresTest extends SapphireTest
{
    protected static $fixture_file = 'ExtendedCaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        BaseCaredHasOneModel::class,
        BaseCaredHasManyModel::class,
        CaresPage::class,
        CaresPageCaredThroughModel::class,
        CaredBelongsToModel::class,
        CaredHasManyModel::class,
        CaredHasOneModel::class,
        CaredManyManyModel::class,
        CaredThroughModel::class,
        ExtendedCaresPage::class,
        ExtendedCaredHasOneModel::class,
        ExtendedCaredHasManyModel::class,
        PolymorphicCaredHasManyModel::class,
        PolymorphicCaredHasManyModel::class,
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

    /**
     * Testing that Base relationships work when the explicit class is used in the relationship
     */
    public function testBaseCaredHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(BaseCaredHasOneModel::class, 'model1');

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

    /**
     * Testing that Base relationships work when the explicit class is used in the relationship
     */
    public function testBaseCaredHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(BaseCaredHasManyModel::class, 'model1');

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

    /**
     * Now testing that a relationship to a Base class still works when the related object is an extended class
     */
    public function testExtendedCaredHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page3');
        // This model is defined on an ExtendedCaresPage relationship that is for BaseCaredHasOneModel. This is a valid
        // class extension that should also trigger an update
        $model = $this->objFromFixture(ExtendedCaredHasOneModel::class, 'model1');

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

    /**
     * Now testing that a relationship to a Base class still works when the related object is an extended class
     */
    public function testExtendedCaredHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page3');
        // This model is defined on an ExtendedCaresPage relationship that is for BaseCaredHasManyModel. This is a valid
        // class extension that should also trigger an update
        $model = $this->objFromFixture(ExtendedCaredHasManyModel::class, 'model1');

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
