<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchesBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchedPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchesPage;

class DotNotationTouchesTest extends SapphireTest
{
    protected static $fixture_file = 'DotNotationTouchesTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        DotNotationTouchedPage::class,
        DotNotationTouchesPage::class,
        DotNotationTouchedBelongsTo::class,
        DotNotationTouchedHasMany::class,
        DotNotationTouchedHasOne::class,
        DotNotationTouchesBelongsTo::class,
    ];

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesHasOne(string $readingMode, string $saveMethod, bool $expectMatch): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedHasOne::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasOne::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedHasOne::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedHasOne::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedHasOneFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedHasOneSecondID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectMatch);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesPureHasOne(string $readingMode, string $saveMethod, bool $expectMatch): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedBelongsTo::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchedBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchedBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchedBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchedBelongsToSecondID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectMatch);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesHasMany(string $readingMode, string $saveMethod, bool $expectMatch): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchesPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchedHasMany::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchedHasMany::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertCount(1, $page->TouchedHasManyFirst());
        $this->assertEquals($page->TouchedHasManyFirst()->first()->ID, $modelOne->ID);
        $this->assertCount(1, $page->TouchedHasManySecond());
        $this->assertEquals($page->TouchedHasManySecond()->first()->ID, $modelTwo->ID);

        $this->assertCacheKeyChanges($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectMatch);
    }

    /**
     * @dataProvider readingModesWithSaveMethods
     */
    public function testTouchesBelongsTo(string $readingMode, string $saveMethod, bool $expectMatch): void
    {
        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $page = $this->objFromFixture(DotNotationTouchedPage::class, 'page1');
        $modelOne = $this->objFromFixture(DotNotationTouchesBelongsTo::class, 'model1');
        $modelTwo = $this->objFromFixture(DotNotationTouchesBelongsTo::class, 'model2');

        // Make sure we publish our records
        $page->publishRecursive();
        $modelOne->publishRecursive();
        $modelTwo->publishRecursive();

        // Check that we're set up correctly
        $this->assertEquals(DotNotationTouchesBelongsTo::class, $modelOne->ClassName);
        $this->assertEquals(DotNotationTouchesBelongsTo::class, $modelTwo->ClassName);
        $this->assertEquals($page->TouchesBelongsToFirstID, $modelOne->ID);
        $this->assertEquals($page->TouchesBelongsToSecondID, $modelTwo->ID);

        Versioned::withVersionedMode(
            function () use ($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectMatch): void {
                Versioned::set_stage($readingMode);

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $originalKey = CacheKey::findInStage($page);

                $this->assertNotNull($originalKey);
                $this->assertNotEmpty($originalKey->KeyHash);

                // Flush updates again before we trigger the next change
                ProcessedUpdatesService::singleton()->flush();

                // Begin changes
                $modelOne->forceChange();
                $modelOne->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($page);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($newKey->KeyHash);

                if ($expectMatch) {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                }

                // Flush updates again before we trigger the next change
                ProcessedUpdatesService::singleton()->flush();

                // Begin changes
                $modelTwo->forceChange();
                $modelTwo->{$saveMethod}();

                // Save our key again before we regenerate it
                $originalKey = $newKey;

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKey = CacheKey::findInStage($page);

                $this->assertNotNull($newKey);
                $this->assertNotEmpty($newKey->KeyHash);

                if ($expectMatch) {
                    $this->assertEquals($originalKey->KeyHash, $newKey->KeyHash);
                } else {
                    $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
                }
            }
        );
    }

    protected function assertCacheKeyChanges(
        DotNotationTouchesPage $page,
        DataObject $modelOne,
        DataObject $modelTwo,
        string $readingMode,
        string $saveMethod,
        bool $expectMatch
    ): void {
        Versioned::withVersionedMode(
            function () use ($page, $modelOne, $modelTwo, $readingMode, $saveMethod, $expectMatch): void {
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

                $page->forceChange();
                $page->{$saveMethod}();

                // Specifically fetching this way to make sure it's us fetching without any generation of KeyHash
                $newKeyOne = CacheKey::findInStage($modelOne);
                $newKeyTwo = CacheKey::findInStage($modelTwo);

                $this->assertNotNull($newKeyOne);
                $this->assertNotNull($newKeyTwo);
                $this->assertNotEmpty($newKeyOne->KeyHash);
                $this->assertNotEmpty($newKeyTwo->KeyHash);

                if ($expectMatch) {
                    $this->assertEquals($originalKeyOne->KeyHash, $newKeyOne->KeyHash);
                    $this->assertEquals($originalKeyTwo->KeyHash, $newKeyTwo->KeyHash);
                } else {
                    $this->assertNotEquals($originalKeyOne->KeyHash, $newKeyOne->KeyHash);
                    $this->assertNotEquals($originalKeyTwo->KeyHash, $newKeyTwo->KeyHash);
                }
            }
        );
    }

    public function readingModesWithSaveMethods(): array
    {
        return [
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the DRAFT stage, we would expect a different value when we fetch that CacheKey again
            'performing write() in DRAFT stage' => [Versioned::DRAFT, 'write', false],
            // If publishRecursive() is performed on a modal, then we expect the same behaviour as above for the DRAFT
            // stage of our CacheKey
            'performing publishRecursive() in DRAFT stage' => [Versioned::DRAFT, 'publishRecursive', false],
            // If write() is performed on a model then we would expect the CacheKey to be updated in DRAFT only. Since
            // we are working in the LIVE stage, we would expect the LIVE value of this CacheKey to be unchanged
            'performing write() in LIVE stage' => [Versioned::LIVE, 'write', true],
            // If publishRecursive() is performed on a modal, then we expect that CacheKey to also be published. As we
            // are working in the LIVE stage, we would now expect a new CacheKey value when it if fetched again
            'performing publishRecursive() in LIVE stage' => [Versioned::LIVE, 'publishRecursive', false],
        ];
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
