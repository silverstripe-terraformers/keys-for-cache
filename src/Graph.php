<?php

namespace Terraformers\KeysForCache;

class Graph
{
    private array $nodes = [];
    private array $edges = [];

    public function addNode(Node $node): self
    {
        $this->nodes[$node->getClassName()] = $node;

        return $this;
    }

    public function getNode(string $className): ?Node
    {
        return $this->nodes[$className] ?? null;
    }

    public function findOrCreateNode(string $className): Node
    {
        $node = $this->getNode($className);

        if (!$node) {
            $node = new Node($className);
            $this->addNode($node);
        }

        return $node;
    }

    public function addEdge(Edge $edge): self
    {
        $this->edges[] = $edge;

        return $this;
    }

    public function getEdges(string $from): array
    {
        return array_filter(
            $this->edges,
            fn($e) => $e->getFromClassName() === $from
        );
    }
}
