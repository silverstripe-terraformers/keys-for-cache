<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

class CacheKeyDto
{
    private ?string $key;

    public function __construct(?string $key)
    {
        $this->key = $key;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /*
     * Append a string to the existing cache key
     */
    public function appendKey(string $value): void
    {
        $this->setKey(sprintf(
            '%s-%s',
            $this->getKey(),
            $value
        ));
    }

    /*
     * Expects an array of string values which will be added to the key
     */
    public function addArrayToKey(array $values): void
    {
        $this->appendKey(implode('-', $values));
    }
}
