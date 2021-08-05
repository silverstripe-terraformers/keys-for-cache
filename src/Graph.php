<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;

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

    public static function build(): Graph
    {
        $graph = new Graph();
        // Relations only exist from data objects
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        foreach ($classes as $className) {
            $config = Config::forClass($className);
            $touches = $config->get('touches') ?? [];
            $cares = $config->get('cares') ?? [];
            $node = $graph->findOrCreateNode($className);

            foreach ($touches as $relation => $touchClassName) {
                $touchNode = $graph->findOrCreateNode($touchClassName);
                $edge = new Edge($node, $touchNode, $relation);
                $graph->addEdge($edge);
            }

            foreach ($cares as $relation => $careClassName) {
                $careNode = $graph->findOrCreateNode($careClassName);
                $edge = new Edge($careNode, $node, $relation);
                $graph->addEdge($edge);
            }
        }

        return $graph;
    }
}
