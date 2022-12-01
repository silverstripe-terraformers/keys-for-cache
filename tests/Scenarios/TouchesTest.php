<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
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

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(TouchedHasOne::class, $model->ClassName);
        $this->assertEquals($page->TouchedHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesTrueHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedBelongsTo::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(TouchedBelongsTo::class, $model->ClassName);
        $this->assertEquals($page->TouchedBelongsToID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testPolymorphicTouchesHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicTouchedHasOne::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(PolymorphicTouchedHasOne::class, $model->ClassName);
        $this->assertEquals($page->PolymorphicHasOneID, $model->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testPolymorphicTouchesHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(PolymorphicTouchedHasMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesManyMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedManyMany::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedManyMany());
        $this->assertEquals($model->ID, $page->TouchedManyMany()->first()->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesThrough(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchesPage::class, 'page1');
        $model = $this->objFromFixture(TouchedThrough::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

        // Check we're set up correctly
        $this->assertCount(1, $page->TouchedThrough());
        $this->assertEquals($model->ID, $page->TouchedThrough()->first()->ID);

        $this->assertCacheKeyChanges($page, $model, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesBelongsTo(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        $page = $this->objFromFixture(TouchedPage::class, 'page1');
        $model = $this->objFromFixture(TouchesBelongsTo::class, 'model1');

        // Make sure our models are published
        $page->publishRecursive();
        $model->publishRecursive();

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
                $this->assertNotEmpty($newKey->KeyHash);

                if ($expectKeyChange) {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                }
            }
        );
    }

    protected function assertCacheKeyChanges(
        TouchesPage $page,
        DataObject $model,
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        Versioned::withVersionedMode(
            function () use ($page, $model, $readingMode, $saveMethod, $expectKeyChange): void {
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKey = CacheKey::findInStage($model);

                $this->assertNotNull($originalKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                // Flush updates again before we trigger the next change
                ProcessedUpdatesService::singleton()->flush();

                // Begin triggering changes
                $page->forceChange();
                $page->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($model);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($newKey->KeyHash);

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
