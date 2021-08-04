<?php

namespace Terraformers\KeysForCache;

class RelationDTO
{
    private array $classes = [];

    public function __construct(array $classes = [])
    {
        $this->classes = $classes;
    }

    public function getRelations(): array
    {
        return $this->classes;
    }

    public function getRelationsForClass(string $className): ?array
    {
        return $this->classes[$className] ?? null;
    }

    public function hasRelationForClass(string $className, string $relationClassName): bool
    {
        if (!array_key_exists($className, $this->classes)) {
            throw new \InvalidArgumentException('This should not happen');
        }

        return in_array($relationClassName, $this->classes[$className]);
    }

    public function setRelation(string $className, array $relations): void
    {
        $this->classes[$className] = array_unique($relations);
    }
}
