<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

class Node
{
    public function __construct(private readonly string $className)
    {
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
