<?php

namespace Terraformers\KeysForCache\Tests\Scenarios;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Tests\Mocks\Pages\GlobalCaresPage;

class GlobalCaresTest extends SapphireTest
{
    protected static $fixture_file = 'GlobalCaresTest.yml'; // phpcs:ignore

    /**
     * phpcs:ignore
     */
    protected static $extra_dataobjects = [
        GlobalCaresPage::class,
    ];

    public function testGlobalCares(): void
    {

        // Updates are processed as part of scaffold, so we need to flush before we kick off
        ProcessedUpdatesService::singleton()->flush();

        $siteConfig = SiteConfig::current_site_config();
        $page = $this->objFromFixture(GlobalCaresPage::class, 'page1');

        // Check we're set up correctly
        $originalKey = $page->getCacheKey();

        $this->assertNotNull($originalKey);
        $this->assertNotEmpty($originalKey);

        // Begin changes
        $siteConfig->forceChange();
        $siteConfig->write();

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
