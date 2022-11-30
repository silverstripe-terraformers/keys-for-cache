<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
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

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresPureHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresBelongsTo(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->CaredBelongsToID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOne::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testPolymorphicCaresHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(PolymorphicCaredHasOne::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testExtendedPolymorphicCaresHasOne(
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(ExtendedPolymorphicCaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(ExtendedPolymorphicCaredHasMany::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testPolymorphicCaresHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testExtendedPolymorphicCaresHasMany(
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(ExtendedPolymorphicCaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * Testing that Base relationships work when the explicit class is used in the relationship
     *
     * @dataProvider readingModesWithSaveMethods
     */
    public function testBaseCaredHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(BaseCaredHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * Testing that Base relationships work when the explicit class is used in the relationship
     *
     * @dataProvider readingModesWithSaveMethods
     */
    public function testBaseCaredHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page2');
        $model = $this->objFromFixture(BaseCaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * Now testing that a relationship to a Base class still works when the related object is an extended class
     *
     * @dataProvider readingModesWithSaveMethods
     */
    public function testExtendedCaredHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page3');
        // This model is defined on an ExtendedCaresPage relationship that is for BaseCaredHasOneModel. This is a valid
        // class extension that should also trigger an update
        $model = $this->objFromFixture(ExtendedCaredHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * Now testing that a relationship to a Base class still works when the related object is an extended class
     *
     * @dataProvider readingModesWithSaveMethods
     */
    public function testExtendedCaredHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(ExtendedCaresPage::class, 'page3');
        // This model is defined on an ExtendedCaresPage relationship that is for BaseCaredHasManyModel. This is a valid
        // class extension that should also trigger an update
        $model = $this->objFromFixture(ExtendedCaredHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    protected function assertCacheKeyChanges(
        ExtendedCaresPage $page,
        DataObject $model,
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        Versioned::withVersionedMode(
            function () use ($page, $model, $readingMode, $saveMethod, $expectKeyChange): void {
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKey = CacheKey::findInStage($page);

                $this->assertNotNull($originalKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                // Flush updates again before we trigger the next change
                ProcessedUpdatesService::singleton()->flush();

                $model->forceChange();
                $model->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($page);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                if ($expectKeyChange) {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                }
            }
        );
    }

    public function readingModesWithSaveMethods(): array
    {
        return [
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the DRAFT stage, we would expect a different value when we fetch that CacheKey again
            'performing write() in DRAFT stage' => [Versioned::DRAFT, 'write', true],
            // If publishRecursive() is performed on a modal, then we expect the same behaviour as above for the DRAFT
            // stage of our CacheKey
            'performing publishRecursive() in DRAFT stage' => [Versioned::DRAFT, 'publishRecursive', true],
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the LIVE stage, we would expect the LIVE value of this CacheKey to be unchanged
            'performing write() in LIVE stage' => [Versioned::LIVE, 'write', false],
            // If publishRecursive() is performed on a modal, then we expect that CacheKey to also be published. As we
            // are working in the LIVE stage, we would now expect a new CacheKey value when it if fetched again
            'performing publishRecursive() in LIVE stage' => [Versioned::LIVE, 'publishRecursive', true],
        ];
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
