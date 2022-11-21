<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneNonVersioned;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaredThrough;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThrough;

class CaresTest extends SapphireTest
{
    protected static $fixture_file = 'CaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        CaresPage::class,
        CaresPageCaredThrough::class,
        CaredBelongsTo::class,
        CaredHasMany::class,
        CaredHasOne::class,
        CaredHasOneNonVersioned::class,
        CaredManyMany::class,
        CaredThrough::class,
        PolymorphicCaredHasOne::class,
        PolymorphicCaredHasMany::class,
    ];

    public function testCaresPureHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
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
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresBelongsTo(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
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
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
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
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testCaresHasOneNonVersioned(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOneNonVersioned::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOneNonVersioned::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneNonVersionedID, $model->ID);

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

    public function testPolymorphicCaresHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
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

    public function testCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasMany::class, 'model1');

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

    public function testPolymorphicCaresHasMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
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

    public function testManyMany(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredManyMany::class, 'model1');

        // Check we're set up correctly
        $this->assertCount(1, $page->CaredManyMany());
        $this->assertEquals($model->ID, $page->CaredManyMany()->first()->ID);

        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin triggering changes
        $model->forceChange();
        $model->write();

        $newKey = $page->getCacheKey();

        $this->assertNotNull($newKey);
        $this->assertNotEmpty($newKey);
        $this->assertNotEquals($originalKey, $newKey);
    }

    public function testManyManyThrough(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredThrough::class, 'model1');

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

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
