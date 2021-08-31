<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;

class Graph
{
    use Injectable;

    private array $nodes = [];
    private array $edges = [];
    private array $global_cares = [];

    public function __construct()
    {
        $this->build();
        $this->createGlobalCares();
    }

    public function getEdges(string $from): array
    {
        return array_filter(
            $this->edges,
            fn($e) => $e->getFromClassName() === $from
        );
    }

    public function getGlobalCares(): array
    {
        return $this->global_cares;
    }

    private function addNode(Node $node): self
    {
        $this->nodes[$node->getClassName()] = $node;

        return $this;
    }

    private function getNode(string $className): ?Node
    {
        return $this->nodes[$className] ?? null;
    }

    private function findOrCreateNode(string $className): Node
    {
        $node = $this->getNode($className);

        if (!$node) {
            $node = new Node($className);
            $this->addNode($node);
        }

        return $node;
    }

    private function addEdge(Edge $edge): self
    {
        $this->edges[] = $edge;

        return $this;
    }

    private function build(): void
    {
        // Relations only exist from data objects
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        foreach ($classes as $className) {
            $config = Config::forClass($className);
            $touches = $config->get('touches') ?? [];
            $cares = $config->get('cares') ?? [];
            $node = $this->findOrCreateNode($className);

            foreach ($touches as $relation => $touchClassName) {
                [$touchClassName, $touchRelation] = $this->getClassAndRelation($touchClassName);

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

                $touchNode = $this->findOrCreateNode($touchClassName);
                $edge = $touchRelation
                    ? new Edge($touchNode, $node, $touchRelation)
                    : new Edge($node, $touchNode, $relation);
                $this->addEdge($edge);
            }

            foreach ($cares as $relation => $careClassName) {
                [$careClassName, $caresRelation] = $this->getClassAndRelation($careClassName);

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

                $careNode = $this->findOrCreateNode($careClassName);
                $edge = $caresRelation
                    ? new Edge($node, $careNode, $caresRelation)
                    : new Edge($careNode, $node, $relation);
                $this->addEdge($edge);
            }
        }
    }

    private function createGlobalCares(): void
    {
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        $classes = array_map(
            fn($c) => ['className' => $c, 'cares' => Config::forClass($c)->get('global_cares')],
            $classes
        );

        $classes = array_filter(
            $classes,
            fn($c) => is_array($c['cares']) && count($c['cares']) > 0
        );

        $classes = array_reduce(
            $classes,
            function($carry, $item) {
                foreach ($item['cares'] as $care) {
                    if (!array_key_exists($care, $carry)) {
                        $carry[$care] = [];
                    }

                    $carry[$care][] = $item['className'];
                }

                return $carry;
            },
            []
        );

        $this->global_cares = $classes;
    }

    private function getClassAndRelation(string $input): array
    {
        $res = explode('.', $input);

        return [$res[0], $res[1] ?? null];
    }
}
