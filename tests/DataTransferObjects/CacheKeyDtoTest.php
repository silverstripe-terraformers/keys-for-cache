<?php

namespace Terraformers\KeysForCache\Tests\DataTransferObjects;

use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\CacheKeyDto;

class CacheKeyDtoTest extends SapphireTest
{
    public function testGet(): void
    {
        $cacheKeyDto = new CacheKeyDto('test');

        $this->assertEquals('test', $cacheKeyDto->getKey());
    }

    public function testSet(): void
    {
        $cacheKeyDto = new CacheKeyDto('test');
        $cacheKeyDto->setKey('test1');

        $this->assertEquals('test1', $cacheKeyDto->getKey());
    }

    public function testAppendKey(): void
    {
        $cacheKeyDto = new CacheKeyDto('test');
        $cacheKeyDto->appendKey(1);

        $this->assertEquals('test-1', $cacheKeyDto->getKey());
    }

    public function testAddArrayToKey(): void
    {
        $cacheKeyDto = new CacheKeyDto('test');
        $cacheKeyDto->addArrayToKey([1, 2, 3]);

        $this->assertEquals('test-1-2-3', $cacheKeyDto->getKey());
    }

    public function testNullCreation(): void
    {
        $cacheKeyDto = new CacheKeyDto(null);

        $this->assertNull($cacheKeyDto->getKey());

        $cacheKeyDto->appendKey(1);

        $this->assertEquals('-1', $cacheKeyDto->getKey());

        $cacheKeyDto = new CacheKeyDto(null);

        $this->assertNull($cacheKeyDto->getKey());

        $cacheKeyDto->addArrayToKey([1, 2, 3]);

        $this->assertEquals('-1-2-3', $cacheKeyDto->getKey());
    }
}
