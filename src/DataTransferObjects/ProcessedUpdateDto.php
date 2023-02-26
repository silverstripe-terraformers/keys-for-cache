<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

class ProcessedUpdateDto
{
    private bool $published = false;

    public function __construct(private readonly string $className, private readonly int $id)
    {
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
