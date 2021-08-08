<?php

namespace Terraformers\KeysForCache;

class Edge
{
    private Node $from;
    private Node $to;
    private string $relation;

    /**
     * @param Node $from
     * @param Node $to
     */
    public function __construct(Node $from, Node $to, string $relation)
    {
        $this->from = $from;
        $this->to = $to;
        $this->relation = $relation;
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
}
