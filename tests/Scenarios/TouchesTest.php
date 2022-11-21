<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchesBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchedPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\TouchedThrough;
use Terraformers\KeysForCache\Tests\Mocks\Relations\TouchesPageTouchedThrough;

class TouchesTest extends SapphireTest
{
    protected static $fixture_file = 'TouchesTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        PolymorphicTouchedHasOne::class,
        PolymorphicTouchedHasMany::class,
        TouchedPage::class,
        TouchesPage::class,
        TouchesPageTouchedThrough::class,
        TouchedBelongsTo::class,
        TouchedHasMany::class,
        TouchedHasOne::class,
        TouchedManyMany::class,
        TouchedThrough::class,
        TouchesBelongsTo::class,
    ];

    public function testTouchesHasOne(): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedHasOne::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(TouchedHasOne::class, $model->ClassName);
        $this->assertEquals($page->TouchedHasOneID, $model->ID);

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
        $model = $this->objFromFixture(TouchedBelongsTo::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(TouchedBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->TouchedBelongsToID, $model->ID);

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
        $model = $this->objFromFixture(PolymorphicTouchedHasOne::class, 'model1');

        // Check that we're set up correctly
        $this->assertEquals(PolymorphicTouchedHasOne::class, $model->ClassName);
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
        $model = $this->objFromFixture(TouchedHasMany::class, 'model1');

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
        $model = $this->objFromFixture(PolymorphicTouchedHasMany::class, 'model1');

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
        $model = $this->objFromFixture(TouchedManyMany::class, 'model1');

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedManyMany());
        $this->assertEquals($model->ID, $page->TouchedManyMany()->first()->ID);

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
        $model = $this->objFromFixture(TouchedThrough::class, 'model1');

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedThrough());
        $this->assertEquals($model->ID, $page->TouchedThrough()->first()->ID);

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
        $model = $this->objFromFixture(TouchesBelongsTo::class, 'model1');

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
