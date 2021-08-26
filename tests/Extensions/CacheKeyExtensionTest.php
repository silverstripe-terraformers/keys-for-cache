<?php

namespace Terraformers\KeysForCache\Tests\Extensions;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\Tests\Mocks\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\NoCachePage;

class CacheKeyExtensionTest extends SapphireTest
{

    protected static $fixture_file = 'CacheKeyExtensionTest.yml';

    public function testWriteGeneratesCacheKey(): void
    {
        // Page config is $has_cache_key = true, so when we write this record it should generate a CacheKey
        $page = CachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // There should be exactly 1
        $this->assertCount(1, $keys);
    }

    public function testWriteDoesNotGenerateCacheKey(): void
    {
        // Page config is $has_cache_key = false, so when we write this record it should not generate a CacheKey
        $page = NoCachePage::create();
        $page->write();

        // Fetch all CacheKeys for this ClassName and ID
        $keys = CacheKey::get()->filter([
            'RecordClass' => $page->ClassName,
            'RecordID' => $page->ID,
        ]);

        // There shouldn't be any
        $this->assertCount(0, $keys);
    }

}
