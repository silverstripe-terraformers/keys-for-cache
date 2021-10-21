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
        $this->processedUpdates[] = $processedUpdate;
    }

    public function findProcessedUpdate(string $className, int $id): ?ProcessedUpdateDto
    {
        foreach ($this->processedUpdates as $processedUpdate) {
            $classNameMatches = $processedUpdate->getClassName() === $className;
            $idMatches = $processedUpdate->getId() === $id;

            if ($idMatches && $classNameMatches) {
                return $processedUpdate;
            }
        }

        return null;
    }

    public function findOrCreateProcessedUpdate(string $className, int $id): ProcessedUpdateDto
    {
        $processedUpdate = $this->findProcessedUpdate($className, $id);

        if ($processedUpdate) {
            return $processedUpdate;
        }

        $processedUpdate = new ProcessedUpdateDto($className, $id);
        $this->processedUpdates[] = $processedUpdate;

        return $processedUpdate;
    }

}
