<?php

namespace Terraformers\KeysForCache\Tests\Services;

use Page;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDto;
use Terraformers\KeysForCache\Services\ProcessedUpdatesService;

class ProcessedUpdatesServiceTest extends SapphireTest
{
    public function testAddProcessedUpdate(): void
    {
        $service = ProcessedUpdatesService::singleton();

        $this->assertCount(0, $service->getProcessedUpdates());

        $service->addProcessedUpdate(new ProcessedUpdateDto(Page::class, 99));
        // There are no checks for duplication between DTOs
        $service->addProcessedUpdate(new ProcessedUpdateDto(Page::class, 98));
        $service->addProcessedUpdate(new ProcessedUpdateDto(Page::class, 98));

        $this->assertCount(3, $service->getProcessedUpdates());
    }

    public function testFindProcessedUpdate(): void
    {
        $className = Page::class;
        $classId = 99;
        $service = ProcessedUpdatesService::singleton();

        // We shouldn't find it
        $this->assertNull($service->findProcessedUpdate($className, $classId));

        // Add the Update
        $service->addProcessedUpdate(new ProcessedUpdateDto($className, $classId));

        $this->assertNotNull($service->findProcessedUpdate($className, $classId));
    }

    public function testFindOrCreateProcessedUpdate(): void
    {
        $className = Page::class;
        $classId = 99;
        $service = ProcessedUpdatesService::singleton();

        // We shouldn't find it
        $this->assertNull($service->findProcessedUpdate($className, $classId));

        // We should find it after using findOrCreate
        $this->assertNotNull($service->findOrCreateProcessedUpdate($className, $classId));
        $this->assertCount(1, $service->getProcessedUpdates());
        // If we call it again, we should find it again, but it shouldn't create any new Updates
        $this->assertNotNull($service->findOrCreateProcessedUpdate($className, $classId));
        $this->assertCount(1, $service->getProcessedUpdates());
    }
}
