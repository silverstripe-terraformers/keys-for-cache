<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

class ProcessedUpdateDTO
{
    private string $className;
    private int $id;

    public function __construct(string $className, int $id)
    {
        $this->className = $className;
        $this->id = $id;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
