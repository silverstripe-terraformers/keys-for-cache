<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

class Edge
{
    public function __construct(
        private readonly Node $from,
        private readonly Node $to,
        private readonly string $relation,
        private readonly string $relationType
    ) {
    }

    public function getFromClassName(): string
    {
        return $this->from->getClassName();
    }

    public function getToClassName(): string
    {
        return $this->to->getClassName();
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function getRelationType(): string
    {
        return $this->relationType;
    }
}
