<?php

namespace Terraformers\KeysForCache\Services;

use SilverStripe\Core\Injector\Injectable;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDTO;

class ProcessedUpdatesService
{
    use Injectable;

    private array $processedUpdates = [];

    public function getProcessedUpdates(): array
    {
        return $this->processedUpdates;
    }

    public function addProcessedUpdate(ProcessedUpdateDTO $processedUpdate): void
    {
        $this->processedUpdates[] = $processedUpdate;
    }

    public function findProcessedUpdate(string $className, int $id): ?ProcessedUpdateDTO
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

    public function findOrCreateProcessedUpdate(string $className, int $id): ProcessedUpdateDTO
    {
        $processedUpdate = $this->findProcessedUpdate($className, $id);

        if ($processedUpdate) {
            return $processedUpdate;
        }

        $processedUpdate = new ProcessedUpdateDTO($className, $id);
        $this->processedUpdates[] = $processedUpdate;

        return $processedUpdate;
    }

}
