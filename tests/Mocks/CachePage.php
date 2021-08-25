<?php

namespace Terraformers\KeysForCache\Tests\Mocks;

use SilverStripe\Dev\TestOnly;
use Page;

class CachePage extends Page implements TestOnly
{
    private static bool $has_cache_key = true;
}
