<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedPolymorphicCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\ExtendedPolymorphicCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaredThrough;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThrough;

class ExtendedCaresTest extends SapphireTest
{
    protected static $fixture_file = 'ExtendedCaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        BaseCaredHasOne::class,
        BaseCaredHasMany::class,
        CaresPage::class,
        CaresPageCaredThrough::class,
        CaredBelongsTo::class,
        CaredHasMany::class,
        CaredHasOne::class,
        CaredManyMany::class,
        CaredThrough::class,
        ExtendedCaresPage::class,
        ExtendedCaredHasOne::class,
        ExtendedCaredHasMany::class,
        ExtendedPolymorphicCaredHasMany::class,
        ExtendedPolymorphicCaredHasOne::class,
        PolymorphicCaredHasMany::class,
        PolymorphicCaredHasOne::class,
    ];

    public function testCaresPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToID, $model->ID);

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
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToID, $model->ID);

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
        $model = $this->objFromFixture(CaredHasOne::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOne::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneID, $model->ID);

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

    public function testPolymorphicCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasOne::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(PolymorphicCaredHasOne::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testExtendedPolymorphicCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(ExtendedPolymorphicCaredHasMany::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(ExtendedPolymorphicCaredHasMany::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasMany::class, 'model1');

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

    public function testPolymorphicCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasMany::class, 'model1');

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testExtendedPolymorphicCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(ExtendedPolymorphicCaredHasMany::class, 'model1');

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
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
        $model = $this->objFromFixture(BaseCaredHasOne::class, 'model1');

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
        $model = $this->objFromFixture(BaseCaredHasMany::class, 'model1');

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
        $model = $this->objFromFixture(ExtendedCaredHasOne::class, 'model1');

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
        $model = $this->objFromFixture(ExtendedCaredHasMany::class, 'model1');

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
