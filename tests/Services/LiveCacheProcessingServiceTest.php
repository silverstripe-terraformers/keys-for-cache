<?php

namespace Terraformers\KeysForCache\Tests\Services;

use Page;
use ReflectionClass;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDto;
use Terraformers\KeysForCache\Services\LiveCacheProcessingService;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;

class LiveCacheProcessingServiceTest extends SapphireTest
{
    public function testShouldPublishUpdates(): void
    {
        $service = LiveCacheProcessingService::singleton();
        $reflectionClass = new ReflectionClass(LiveCacheProcessingService::class);
        $method = $reflectionClass->getMethod('shouldPublishUpdates');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service));
    }

    public function testAlreadyProcessed(): void
    {
        $className = Page::class;
        $classId = 99;
        $service = LiveCacheProcessingService::singleton();
        $processedUpdates = ProcessedUpdatesService::singleton();

        // Use ReflectionClass to make the alreadyProcessed() method accessible
        $reflectionClass = new ReflectionClass(LiveCacheProcessingService::class);
        $method = $reflectionClass->getMethod('alreadyProcessed');
        $method->setAccessible(true);

        // We have not added this ProcessedResult, so it should be false
        $this->assertFalse($method->invoke($service, $className, $classId));

        // Add our ProcessedResult
        $update = new ProcessedUpdateDto($className, $classId);
        $processedUpdates->addProcessedUpdate($update);

        // This should still be false, because our ProcessedUpdate is not published
        $this->assertFalse($method->invoke($service, $className, $classId));

        // Publish the update
        $update->setPublished();

        // This should now be true
        $this->assertTrue($method->invoke($service, $className, $classId));
    }
}
