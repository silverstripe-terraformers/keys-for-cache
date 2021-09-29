<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

class Edge
{
    private Node $from;
    private Node $to;
    private string $relation;
    private string $relationType;

    public function __construct(Node $from, Node $to, string $relation, string $relationType)
    {
        $this->from = $from;
        $this->to = $to;
        $this->relation = $relation;
        $this->relationType = $relationType;
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
