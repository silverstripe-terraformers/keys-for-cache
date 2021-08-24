<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

class ProcessedUpdateDTO
{
    private string $className;

    private int $id;

    private bool $published = false;

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

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(): void
    {
        $this->published = true;
    }
}
