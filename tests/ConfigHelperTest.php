<?php

namespace Terraformers\KeysForCache\Tests;

use App\Models\MenuItem;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\ConfigHelper;

class ConfigHelperTest extends SapphireTest
{

    protected $usesDatabase = true;

    public function testGetConfigForName(): void
    {
        $configs = ConfigHelper::getAllConfigsForName('cache_dependencies');

        Debug::dump($configs);
    }

    public function testGetConfigDependents(): void
    {
        $configs = ConfigHelper::getGlobalCacheDependencies(MenuItem::class, 'cache_dependencies');

        Debug::dump($configs);
    }

    public function testGetOwnedByDependencies(): void
    {
        Debug::dump(ConfigHelper::getOwnedByHasOnes(MenuItem::class));
    }

}
