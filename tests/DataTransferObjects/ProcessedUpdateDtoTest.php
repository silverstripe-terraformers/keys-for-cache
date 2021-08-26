<?php

namespace Terraformers\KeysForCache\Tests\DataTransferObjects;

use Page;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDto;

class ProcessedUpdateDtoTest extends SapphireTest
{
    public function testConstructAndGet(): void
    {
        $processedUpdateDto = new ProcessedUpdateDto(Page::class, 99);

        $this->assertEquals(Page::class, $processedUpdateDto->getClassName());
        $this->assertEquals(99, $processedUpdateDto->getId());
    }

    public function testPublishedState(): void
    {
        $processedUpdateDto = new ProcessedUpdateDto(Page::class, 99);

        $this->assertFalse($processedUpdateDto->isPublished());

        $processedUpdateDto->setPublished();

        $this->assertTrue($processedUpdateDto->isPublished());
    }
}
