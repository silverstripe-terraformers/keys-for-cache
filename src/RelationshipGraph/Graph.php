<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

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
                [$touchClassName, $touchRelation] = self::getClassAndRelation($touchClassName);

                // No dot notation so we need to check if this is a has_many, and if it is, we need to find the has_one
                // field on the other side of this relationship
                if (!$touchRelation) {
                    $hasMany = $config->get('has_many');

                    // Has many exists for the relation
                    if (array_key_exists($relation, $hasMany)) {
                        $hasOnes = Config::forClass($careClassName)->get('has_one');

                        foreach ($hasOnes as $hasOneRelation => $hasOneClassName) {
                            if ($hasOneClassName !== $className) {
                                continue;
                            }

                            $touchRelation = $hasOneRelation;
                        }
                    }
                }

                $touchNode = $graph->findOrCreateNode($touchClassName);
                $edge = $touchRelation
                    ? new Edge($touchNode, $node, $touchRelation)
                    : new Edge($node, $touchNode, $relation);
                $graph->addEdge($edge);
            }

            foreach ($cares as $relation => $careClassName) {
                [$careClassName, $caresRelation] = self::getClassAndRelation($careClassName);

                // No dot notation so we need to check if this is a has_many, and if it is, we need to find the has_one
                // field on the other side of this relationship
                if (!$caresRelation) {
                    $hasMany = $config->get('has_many');

                    // Has many exists for the relation
                    if (array_key_exists($relation, $hasMany)) {
                        $hasOnes = Config::forClass($careClassName)->get('has_one');

                        foreach ($hasOnes as $hasOneRelation => $hasOneClassName) {
                            if ($hasOneClassName !== $className) {
                                continue;
                            }

                            $caresRelation = $hasOneRelation;
                        }
                    }
                }

                $careNode = $graph->findOrCreateNode($careClassName);
                $edge = $caresRelation
                    ? new Edge($node, $careNode, $caresRelation)
                    : new Edge($careNode, $node, $relation);
                $graph->addEdge($edge);
            }
        }

        return $graph;
    }

    private static function getClassAndRelation(string $input): array
    {
        $res = explode('.', $input);

        return [$res[0], $res[1] ?? null];
    }
}
