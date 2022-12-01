<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

class DotNotationCaresTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationCaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        DotNotationCaresPage::class,
        DotNotationCaredBelongsTo::class,
        DotNotationCaredHasMany::class,
        DotNotationCaredHasOne::class,
    ];

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresPureHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToSecondID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresBelongsTo(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredBelongsTo::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredBelongsToSecondID, $modelTwo->ID);

        Versioned::withVersionedMode(
            function () use ($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectKeyChange): void {
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKeyOne = CacheKey::findInStage($modelOne);
                $originalKeyTwo = CacheKey::findInStage($modelTwo);

                $this->assertNotNull($originalKeyOne);
                $this->assertNotNull($originalKeyTwo);
                $this->assertNotEmpty($originalKeyOne->KeyHash);
                $this->assertNotEmpty($originalKeyTwo->KeyHash);

                // Flush updates again before we trigger another update
                ProcessedUpdatesService::singleton()->flush();

                // Begin changes
                $page->forceChange();
                $page->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKeyOne = CacheKey::findInStage($modelOne);
                $newKeyTwo = CacheKey::findInStage($modelTwo);

                $this->assertNotNull($newKeyOne);
                $this->assertNotNull($newKeyTwo);
                $this->assertNotEmpty($newKeyOne->KeyHash);
                $this->assertNotEmpty($newKeyTwo->KeyHash);

                if ($expectKeyChange) {
                    $this->assertNotEquals($originalKeyOne->KeyHash, $newKeyOne->KeyHash);
                    $this->assertNotEquals($originalKeyTwo->KeyHash, $newKeyTwo->KeyHash);
                } else {
                    $this->assertEquals($originalKeyOne->KeyHash, $newKeyOne->KeyHash);
                    $this->assertEquals($originalKeyTwo->KeyHash, $newKeyTwo->KeyHash);
                }
            }
        );
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresHasOne(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredHasOne::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasOne::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationCaredHasOne::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationCaredHasOne::class, $modelTwo->ClassName);
        $this->assertEquals($page->CaredHasOneFirstID, $modelOne->ID);
        $this->assertEquals($page->CaredHasOneSecondID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectKeyChange);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testCaresHasMany(string $readingMode, string $saveMethod, bool $expectKeyChange): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationCaresPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationCaredHasMany::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationCaredHasMany::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertCount(1, $page->CaredHasManyFirst());
        $this->assertEquals($page->CaredHasManyFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->CaredHasManySecond());
        $this->assertEquals($page->CaredHasManySecond()->first()->ID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectKeyChange);
    }

    protected function assertCacheKeyChanges(
        DotNotationCaresPage $page,
        DataObject $modelOne,
        DataObject $modelTwo,
        string $readingMode,
        string $saveMethod,
        bool $expectKeyChange
    ): void {
        Versioned::withVersionedMode(
            function () use ($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectKeyChange): void {
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKey = CacheKey::findInStage($page);

                $this->assertNotNull($originalKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                // Flush updates again before we trigger another update
                ProcessedUpdatesService::singleton()->flush();

                $modelOne->forceChange();
                $modelOne->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($page);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                if ($expectKeyChange) {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                }

                // Flush updates again before we trigger another update
                ProcessedUpdatesService::singleton()->flush();

                // Update original key for the next change
                $originalKey = $newKey;

                $modelTwo->forceChange();
                $modelTwo->{$saveMethod}();

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
