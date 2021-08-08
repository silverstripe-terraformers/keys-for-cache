<?php

namespace Terraformers\KeysForCache;

class Node
{
    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
