<?php

namespace Terraformers\KeysForCache\Tests\Models;

use SilverStripe\Dev\TestOnly;
use Page;

class NoCachePage extends Page implements TestOnly
{

    private static bool $has_cache_key = false;

}
