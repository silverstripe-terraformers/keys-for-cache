<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

use Exception;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;
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
            fn(Edge $e) => $e->getFromClassName() === $from
        );
    }

    public function getGlobalCares(): array
    {
        return $this->global_cares;
    }

    public function flush(): void
    {
        $this->nodes = [];
        $this->edges = [];
        $this->global_cares = [];
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

    private function getClassAndRelation(string $input): array
    {
        $res = explode('.', $input);

        return [$res[0], $res[1] ?? null];
    }

    private function getRelationshipConfig(?array $keys, Config_ForClass $config): array
    {
        if (!$keys) {
            return [];
        }

        $relationshipConfigs = array_merge(
            $config->get('has_one') ?? [],
            $config->get('has_many') ?? [],
            $config->get('belongs_to') ?? [],
        );

        return array_filter(
            $relationshipConfigs,
            function ($relationship) use ($keys) {
                return in_array($relationship, $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getRelationshipForClassName(string $className, string $relationship, ?array $config): ?string
    {
        if (!$config) {
            return null;
        }

        foreach ($config as $relation => $relationString) {
            [$relationClassName, $relationField] = $this->getClassAndRelation($relationString);

            if ($relationClassName !== $className) {
                continue;
            }

            // This relation matches the class and there is no dot notation, indicating that it is the only relationship
            // available. We can return here
            if (!$relationField) {
                return $relation;
            }

            // There is a dot notation, and this $relationField does not match the expected $relationship
            if ($relationField !== $relationship) {
                continue;
            }

            return $relation;
        }

        return null;
    }

    private function build(): void
    {
        // Relations only exist from data objects
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        foreach ($classes as $className) {
            $config = Config::forClass($className);
            $touches = $this->getRelationshipConfig($config->get('touches'), $config);
            $cares = $this->getRelationshipConfig($config->get('cares'), $config);
            $node = $this->findOrCreateNode($className);

            // $touches Edges always need to go $from this class $to the class that they touch
            foreach ($touches as $relation => $touchClassName) {
                [$touchClassName] = $this->getClassAndRelation($touchClassName);

                $touchNode = $this->findOrCreateNode($touchClassName);
                $edge = new Edge($node, $touchNode, $relation);
                $this->addEdge($edge);
            }

            // $cares Edges always need to go $from the class being cared about $to this class
            foreach ($cares as $relation => $careClassName) {
                [$careClassName, $caresRelation] = $this->getClassAndRelation($careClassName);

                // A dot notation is available, so we can map this immediately and continue
                if ($caresRelation) {
                    $careNode = $this->findOrCreateNode($careClassName);
                    $this->addEdge(new Edge($careNode, $node, $caresRelation));

                    continue;
                }

                // No dot notation was available, so we need to figure out the relationship ourselves

                // Before we get too far, we'll make sure this isn't a many_many, as that is not currently supported
                if (array_key_exists($relation, $config->get('man_many') ?? [])) {
                    // TODO add support for many_many and belongs_many_many
                    continue;
                }

                $has_many = array_key_exists($relation, $config->get('has_many'));
                $belongs_to = array_key_exists($relation, $config->get('belongs_to'));

                // If this relationship is a has_many or a belongs_to, then we need to find the has_one on the other
                // side of the relationship. This relationship should always exist; if it doesn't, then that is invalid
                // ORM config
                if ($has_many || $belongs_to) {
                    $caresRelation = $this->getRelationshipForClassName(
                        $className,
                        $relation,
                        Config::forClass($careClassName)->get('has_one')
                    );

                    if (!$caresRelation) {
                        throw new Exception(sprintf(
                            'No valid has_one found between %s and %s for %s relationship %s',
                            $careClassName,
                            $className,
                            $has_many ? 'has_many' : 'belongs_to',
                            $relation
                        ));
                    }

                    $careNode = $this->findOrCreateNode($careClassName);
                    $this->addEdge(new Edge($careNode, $node, $caresRelation));

                    continue;
                }

                // This relationship is a has_one, so it could be a belongs_to <-> has_one, or has_one <-> has_many
                // We'll first check to see if it is a has_many
                $caresRelation = $this->getRelationshipForClassName(
                    $className,
                    $relation,
                    Config::forClass($careClassName)->get('has_many')
                );

                // Yes, it was a has_many on the other end of the relationship. We can add this Edge and continue
                if ($caresRelation) {
                    $careNode = $this->findOrCreateNode($careClassName);
                    $this->addEdge(new Edge($careNode, $node, $caresRelation));

                    continue;
                }

                // The only remaining possibility is that this is a belongs_to on the other end of this relationship
                // (a has_one <-> has_one)
                $caresRelation = $this->getRelationshipForClassName(
                    $className,
                    $relation,
                    Config::forClass($careClassName)->get('belongs_to')
                );

                if (!$caresRelation) {
                    throw new Exception(sprintf(
                        'No valid belongs_to found between %s and %s for has_many relationship %s',
                        $careClassName, $className, $relation
                    ));
                }

                $careNode = $this->findOrCreateNode($careClassName);
                $this->addEdge(new Edge($careNode, $node, $caresRelation));
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
}
