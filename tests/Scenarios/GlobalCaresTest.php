<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Pages\GlobalCaresPage;

class GlobalCaresTest extends SapphireTest
{
    protected static $fixture_file = 'GlobalCaresTest.yml'; // phpcs:ignore

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array
     */
    protected static $extra_dataobjects = [
        GlobalCaresPage::class,
    ];

    /**
     * @dataProvider readingModes
     */
    public function testGlobalCares(string $readingMode): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $page = $this->objFromFixture(GlobalCaresPage::class, 'page1');

        // Make sure our page is published
        $page->publishRecursive();

        Versioned::withVersionedMode(function () use ($page, $siteConfig, $readingMode): void {
            Versioned::set_stage($readingMode);

            // Check we're set up correctly
            $originalKey = CacheKey::findInStage($page);

            $this->assertNotNull($originalKey);
            $this->assertNotEmpty($originalKey->KeyHash);

            // Updates are processed as part of scaffold, so we need to flush before we kick off
            ProcessedUpdatesService::singleton()->flush();

            // Begin changes
            $siteConfig->forceChange();
            $siteConfig->write();

            $newKey = CacheKey::findInStage($page);

            // Global cares work by simply deleting the CacheKeys, so we would expect this to be null initially
            $this->assertNull($newKey);

            // Once we have run getCacheKey() we should have generated a new Key
            $page->getCacheKey();

            $newKey = CacheKey::findInStage($page);

            // We would now expect it to exist
            $this->assertNotNull($newKey);
            $this->assertNotEmpty($originalKey->KeyHash);
            $this->assertNotEquals($originalKey->KeyHash, $newKey->KeyHash);
        });
    }

    public function readingModes(): array
    {
        return [
            [Versioned::DRAFT],
            [Versioned::LIVE],
        ];
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
