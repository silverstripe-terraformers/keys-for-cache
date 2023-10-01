<?php

namespace Terraformers\KeysForCache\Services;

use SilverStripe\Core\Injector\Injectable;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDto;

class ProcessedUpdatesService
{

    use Injectable;

    private array $processedUpdates = [];

    public function flush(): void
    {
        $this->processedUpdates = [];
    }

    public function getProcessedUpdates(): array
    {
        return $this->processedUpdates;
    }

    public function addProcessedUpdate(ProcessedUpdateDto $processedUpdate): void
    {
        $key = $this->getProcessedUpdateKey($processedUpdate->getClassName(), $processedUpdate->getId());
        $this->processedUpdates[$key] = $processedUpdate;
    }

    public function findProcessedUpdate(string $className, int $id): ?ProcessedUpdateDto
    {
        $key = $this->getProcessedUpdateKey($className, $id);

        return $this->processedUpdates[$key] ?? null;
    }

    public function findOrCreateProcessedUpdate(string $className, int $id): ProcessedUpdateDto
    {
        $processedUpdate = $this->findProcessedUpdate($className, $id);

        if ($processedUpdate) {
            return $processedUpdate;
        }

        $processedUpdate = new ProcessedUpdateDto($className, $id);
        $this->addProcessedUpdate($processedUpdate);

        return $processedUpdate;
    }

    private function getProcessedUpdateKey(string $className, int $id): string
    {
        return sprintf('%s-%s', $className, $id);
    }

}
