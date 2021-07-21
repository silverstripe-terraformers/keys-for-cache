<?php

namespace Terraformers\KeysForCache\Tests;

use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\ConfigHelper;

class ConfigHelperTest extends SapphireTest
{

    protected $usesDatabase = true;

    public function testGetConfigForName(): void
    {
        $configs = ConfigHelper::getConfigForName('db');

        Debug::dump($configs);
    }

}
