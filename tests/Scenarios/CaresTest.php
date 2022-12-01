<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneNonVersioned;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneVersionedNonStaged;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaredThrough;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThrough;

/**
 * These tests are specifically about checking that our triggers are actions as we expect. We will intentionally
 * *avoid* using the getCacheKey() method on our Models so that we know we aren't generating any CacheKeys on request
 */
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
        CaredHasOneVersionedNonStaged::class,
        CaredManyMany::class,
        CaredThrough::class,
        PolymorphicCaredHasOne::class,
        PolymorphicCaredHasMany::class,
    ];

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresPureHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Make sure our page and model are published
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

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredBelongsTo::class, 'model1');

        // Make sure our page and model are published
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

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOne::class, 'model1');

        // Make sure our page and model are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOne::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModes
     */
    public function testCaresHasOneNonVersioned(string $readingMode): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOneNonVersioned::class, 'model1');

        // Make sure our page is published (the model is not Versioned)
        $page->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOneNonVersioned::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneNonVersionedID, $model->ID);

        Versioned::withVersionedMode(function () use ($page, $model, $readingMode): void {
            Versioned::set_stage($readingMode);

            // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
            $originalKey = CacheKey::findInStage($page);

            // Check that we're set up with an initial KeyHash
            $this->assertNotNull($originalKey);
            $this->assertNotEmpty($originalKey->KeyHash);

            // Flush updates so that new changes generate new CacheKey hashes
            ProcessedUpdatesService::singleton()->flush();

            // Begin changes
            $model->forceChange();
            $model->write();

            // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
            $newKey = CacheKey::findInStage($page);

            $this->assertNotNull($newKey);
            $this->assertNotEmpty($newKey->KeyHash);
            $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
        });
    }

    /**
     * @dataProvider readingModes
     */
    public function testCaresHasOneVersionedNonStaged(string $readingMode): void
    {
        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasOneVersionedNonStaged::class, 'model1');

        // Make sure our page is published (the model is not Versioned)
        $page->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(CaredHasOneVersionedNonStaged::class, $model->ClassName);
        $this->assertEquals($page->CaredHasOneVersionedNonStagedID, $model->ID);

        Versioned::withVersionedMode(function () use ($page, $model, $readingMode): void {
            // We perform our save method and read in the same reading mode
            Versioned::set_stage($readingMode);

            // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
            $originalKey = CacheKey::findInStage($page);

            $this->assertNotNull($originalKey);
            $this->assertNotEmpty($originalKey);

            // Flush updates so that new changes generate new CacheKey hashes
            ProcessedUpdatesService::singleton()->flush();

            // Begin changes
            $model->forceChange();
            $model->write();

            // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
            $newKey = CacheKey::findInStage($page);

            $this->assertNotNull($newKey);
            $this->assertNotEmpty($newKey);
            $this->assertNotEquals($originalKey, $newKey);
        });
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testPolymorphicCaresHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasOne::class, 'model1');

        // Make sure our page and model are published
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
    public function testCaresHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredHasMany::class, 'model1');

        // Make sure our page and model are published
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

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicCaredHasMany::class, 'model1');

        // Make sure our page and model are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testManyMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredManyMany::class, 'model1');

        // Make sure our page and model are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check we're set up correctly
        $this->assertCount(1, $page->CaredManyMany());
        $this->assertEquals($model->ID, $page->CaredManyMany()->first()->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testManyManyThrough(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(CaresPage::class, 'page1');
        $model = $this->objFromFixture(CaredThrough::class, 'model1');

        // Make sure our page and model are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    protected function assertCacheKeyChanges(
        CaresPage $page,
        DataObject $model,
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        Versioned::withVersionedMode(
            function () use ($page, $model, $readingMode, $saveMethod, $expectKeyChange): void {
                // We perform our save method and read in the same reading mode
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKey = CacheKey::findInStage($page);

                // Check that we're set up with an initial KeyHash
                $this->assertNotNull($originalKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                // Flush updates so that new changes generate new CacheKey hashes
                ProcessedUpdatesService::singleton()->flush();

                $model->forceChange();
                // @see readingModesWithSaveMethods() - write() or publishRecursive() depending on the test
                // We are performing this test across both reading modes, but we expect CacheKeys to respect the action,
                // rather than the reading mode (that being, write() creates DRAFT, and publish() creates LIVE)
                $model->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($page);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($newKey->KeyHash);

                // @see readingModesWithSaveMethods() for when (and why) we expect changes to our KeyHash
                if ($expectKeyChange) {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                }
            }
        );
    }

    public function readingModes(): array
    {
        return [
            [Versioned::DRAFT],
            [Versioned::LIVE],
        ];
    }

    public function readingModesWithSaveMethods(): array
    {
        return [
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the DRAFT stage, we would expect a different value when we fetch that CacheKey again
            'performing write() in DRAFT stage' => [Versioned::DRAFT, 'write', true],
            // If publishRecursive() is performed on a model, then we expect the same behaviour as above for the DRAFT
            // stage of our CacheKey
            'performing publishRecursive() in DRAFT stage' => [Versioned::DRAFT, 'publishRecursive', true],
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the LIVE stage, we would expect the LIVE value of this CacheKey to be unchanged
            'performing write() in LIVE stage' => [Versioned::LIVE, 'write', false],
            // If publishRecursive() is performed on a model, then we expect that CacheKey to also be published. As we
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
