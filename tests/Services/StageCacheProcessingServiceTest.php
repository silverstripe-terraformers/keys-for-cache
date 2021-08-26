<?php

namespace Terraformers\KeysForCache\Tests\Services;

use Page;
use ReflectionClass;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDto;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;
use Terraformers\KeysForCache\Services\StageCacheProcessingService;

class StageCacheProcessingServiceTest extends SapphireTest
{
    public function testShouldPublishUpdates(): void
    {
        $service = StageCacheProcessingService::singleton();
        $reflectionClass = new ReflectionClass(StageCacheProcessingService::class);
        $method = $reflectionClass->getMethod('shouldPublishUpdates');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service));
    }

    public function testAlreadyProcessed(): void
    {
        $className = Page::class;
        $classId = 99;
        $service = StageCacheProcessingService::singleton();
        $processedUpdates = ProcessedUpdatesService::singleton();

        // Use ReflectionClass to make the alreadyProcessed() method accessible
        $reflectionClass = new ReflectionClass(StageCacheProcessingService::class);
        $method = $reflectionClass->getMethod('alreadyProcessed');
        $method->setAccessible(true);
        // Invoke the method and save the result
        $result = $method->invoke($service, $className, $classId);

        // We have not added this ProcessedResult, so it should be false
        $this->assertFalse($result);

        // Add our ProcessedResult
        $update = new ProcessedUpdateDto($className, $classId);
        $processedUpdates->addProcessedUpdate($update);

        // Invoke the method again and save the result
        $result = $method->invoke($service, $className, $classId);

        // This should now be true
        $this->assertTrue($result);
    }
}
