<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

use Exception;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ViewableData;
use Terraformers\KeysForCache\Models\CacheKey;

class Graph implements Flushable
{

    use Injectable;

    public const CACHE_KEY = CacheInterface::class . '.KeysForCache';
    public const CACHE_KEY_EDGES = 'Edges';
    public const CACHE_KEY_GLOBAL_CARES = 'GlobalCares';

    private array $nodes = [];
    private array $edges = [];
    private array $global_cares = [];

    public function __construct()
    {
        // Make sure that our Edges and GlobalCares are built and available
        $this->buildEdges();
        $this->buildGlobalCares();
    }

    public static function flush() // phpcs:ignore SlevomatCodingStandard.TypeHints
    {
        Injector::inst()->get(self::CACHE_KEY)->clear();

        $inst = static::singleton();
        $inst->buildEdges();
        $inst->buildGlobalCares();
    }

    public function getEdgesFrom(string $from): array
    {
        $ancestry = ClassInfo::ancestry($from);
        // Base classes that show up in every ancestry array
        $disallowList = [
            DataObject::class,
            ViewableData::class,
        ];

        return array_filter(
            $this->getEdges(),
            static function (Edge $e) use ($ancestry, $disallowList) {
                return in_array($e->getFromClassName(), $ancestry, true)
                    && !in_array($e->getFromClassName(), $disallowList, true);
            }
        );
    }

    public function getEdges(): array
    {
        return $this->edges;
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

    private function getClassAndRelation(string $input): array
    {
        $res = explode('.', $input);

        return [$res[0], $res[1] ?? null];
    }

    private function getRelationshipConfig(?array $relationships, Config_ForClass $config): array
    {
        if (!$relationships) {
            return [];
        }

        $relationshipConfigs = array_merge(
            $config->get('has_one') ?? [],
            $config->get('has_many') ?? [],
            $config->get('belongs_to') ?? [],
            $config->get('many_many') ?? [],
            $config->get('belongs_many_many') ?? [],
        );

        return array_filter(
            $relationshipConfigs,
            static function ($relationship) use ($relationships) {
                return in_array($relationship, $relationships, true);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param array|null $originConfig The config for the class you wish to find the relationship on
     * @param string $destinationClassName The name of the class you wish the find the relationship for
     * @param string $destinationRelation The name of the relationship at the destination (important for dot notation)
     * @param bool $strict For polymorphic relationships, we need to be very strict on relationship linkages
     * @return string|null
     */
    private function getRelationForClassName(
        ?array $originConfig,
        string $destinationClassName,
        string $destinationRelation,
        bool $strict = false
    ): ?string {
        if (!$originConfig) {
            return null;
        }

        foreach ($originConfig as $relation => $relationString) {
            // This must be a through definition, so the relationship class name will live in the Through model. We
            // do not support traversal in this way
            // @see getCaresManyManyThroughEdge()
            // @see getTouchesManyManyThroughEdge()
            if (is_array($relationString)) {
                continue;
            }

            [$relationClassName, $relationField] = $this->getClassAndRelation($relationString);

            // Using is_a() here so that we find relationships to descendent classes as well
            if (!is_a(Injector::inst()->get($destinationClassName), $relationClassName)) {
                continue;
            }

            // This relation matches the class and there is no dot notation, this would indicate that this is the only
            // relationship available, but we do also need to make sure that this isn't a $strict call, as when we're
            // checking for Polymorphic relationships, this *needs* to be dot notated
            if (!$relationField && !$strict) {
                return $relation;
            }

            // There is a dot notation, and this $relationField does not match the expected $relationship
            if ($relationField !== $destinationRelation) {
                continue;
            }

            return $relation;
        }

        return null;
    }

    /**
     * Given a relation name, determine the relation type
     *
     * @return string has_one, has_many, many_many, belongs_many_many or belongs_to
     */
    private function getRelationType(string $className, string $relation): ?string
    {
        $types = ['has_one', 'has_many', 'many_many', 'belongs_many_many', 'belongs_to'];

        foreach ($types as $type) {
            $relations = Config::inst()->get($className, $type);

            if ($relations && isset($relations[$relation])) {
                return $type;
            }
        }

        return null;
    }

    /**
     * All DataObject classes except those in ignore list
     *
     * @return array
     */
    private function getValidClasses(): array
    {
        // Relations only exist from data objects
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        // Don't build edges for classes in the ignorelist
        $ignoreList = Config::forClass(CacheKey::class)->get('ignorelist');

        return array_filter(
            $classes,
            static function ($class) use ($ignoreList) {
                return !in_array($class, $ignoreList, true);
            },
        );
    }

    private function buildEdges(): void
    {
        $cache = Injector::inst()->get(self::CACHE_KEY);

        if ($cache->has(self::CACHE_KEY_EDGES)) {
            $this->edges = $cache->get(self::CACHE_KEY_EDGES);

            return;
        }

        $classes = $this->getValidClasses();

        // The Edges that we're about to create
        $edges = [];

        // Track any relationship errors that we find
        $errors = [];

        foreach ($classes as $className) {
            $config = Config::forClass($className);
            $touches = $this->getRelationshipConfig($config->get('touches'), $config);
            $cares = $this->getRelationshipConfig($config->get('cares'), $config);
            $node = $this->findOrCreateNode($className);

            // $touches Edges always need to go $from this class $to the class that they touch
            foreach ($touches as $relation => $touchClassName) {
                $manyMany = $config->get('many_many') ?? [];

                // many_many through definitions are a bit of a special case
                if (array_key_exists($relation, $manyMany) && is_array($touchClassName)) {
                    $edge = $this->getTouchesManyManyThroughEdge($node, $relation, $touchClassName);

                    // There is a chance that there was no valid Edge (for a reason that we do understand, and did not
                    // want to throw an error for)
                    if ($edge) {
                        $edges[] = $edge;
                    }

                    continue;
                }

                [$touchClassName] = $this->getClassAndRelation($touchClassName);

                $touchNode = $this->findOrCreateNode($touchClassName);
                $edge = new Edge($node, $touchNode, $relation, $this->getRelationType($className, $relation));
                $edges[] = $edge;
            }

            // $cares Edges always need to go $from the class being cared about $to this class
            foreach ($cares as $relation => $careClassName) {
                // many_many are quite a complex beast as they can optionally include through relationship data
                // many_many and belongs_many_many are treated the same way
                if (array_key_exists($relation, $config->get('many_many') ?? [])
                    || array_key_exists($relation, $config->get('belongs_many_many') ?? [])
                ) {
                    // We process these differently if they have a "through" definition
                    $edge = is_array($careClassName)
                        ? $this->getCaresManyManyThroughEdge($node, $relation, $careClassName)
                        : $this->getCaresManyManyEdge($node, $relation, $careClassName);

                    // There is a chance that there was no valid Edge (for a reason that we do understand, and did not
                    // want to throw an error for)
                    if ($edge) {
                        $edges[] = $edge;
                    }

                    continue;
                }

                // Now that we know the relationship isn't a many_many, we can start processing our more straight
                // forward relationship types
                [$careClassName, $caresRelation] = $this->getClassAndRelation($careClassName);

                // A dot notation is available, so we can map this immediately and continue
                if ($caresRelation) {
                    $relationType = $this->getRelationType($careClassName, $caresRelation);

                    if (!$relationType) {
                        $errors[] = sprintf(
                            'Dot notation %s provided for %s::%s, but no corresponding relationship found in %s.'
                                . ' You might need to add a has_many, has_one, belongs_to, etc in %s',
                            sprintf('%s.%s', $careClassName, $caresRelation),
                            $className,
                            $relation,
                            $careClassName,
                            $careClassName
                        );

                        continue;
                    }

                    $careNode = $this->findOrCreateNode($careClassName);
                    $edges[] = new Edge($careNode, $node, $caresRelation, $relationType);

                    continue;
                }

                // No dot notation was available, so we need to figure out the relationship ourselves

                $has_many = array_key_exists($relation, $config->get('has_many') ?? []);
                $belongs_to = array_key_exists($relation, $config->get('belongs_to') ?? []);

                // If this relationship is a has_many or a belongs_to, then we need to find the has_one on the other
                // side of the relationship. This relationship should always exist; if it doesn't, then that is invalid
                // ORM config
                if ($has_many || $belongs_to) {
                    $caresRelation = $this->getRelationForClassName(
                        Config::forClass($careClassName)->get('has_one') ?? [],
                        $className,
                        $relation
                    );

                    if (!$caresRelation) {
                        $errors[] = sprintf(
                            'No valid has_one found between %s and %s for %s relationship %s',
                            $careClassName,
                            $className,
                            $has_many ? 'has_many' : 'belongs_to',
                            $relation
                        );

                        continue;
                    }

                    $careNode = $this->findOrCreateNode($careClassName);
                    $edges[] = new Edge(
                        $careNode,
                        $node,
                        $caresRelation,
                        $this->getRelationType($careClassName, $caresRelation)
                    );

                    continue;
                }

                // This relationship is a has_one, so it could be a belongs_to <-> has_one, or has_one <-> has_many,
                // it could also be a polymorphic has_one <-> has_many

                // We'll first check to see if it is a polymorphic relationship, as that one's a bit different.
                // Thankfully, we can just check if the $careClassName is DataObject
                if ($careClassName === DataObject::class) {
                    // Yes, it's a polymorphic relationship. This means that we have to go through every config for
                    // every DataObject to check if it references our model
                    foreach ($this->getValidClasses() as $polymorphicClassName) {
                        // Check to see if there is a $strict relationship back to this class
                        $caresRelation = $this->getRelationForClassName(
                            Config::forClass($polymorphicClassName)->get('has_many') ?? [],
                            $className,
                            $relation,
                            true
                        );

                        // No relationship for this class
                        if (!$caresRelation) {
                            continue;
                        }

                        // There is a relationship, so let's build an Edge for it
                        $careNode = $this->findOrCreateNode($polymorphicClassName);
                        $edges[] = new Edge(
                            $careNode,
                            $node,
                            $caresRelation,
                            $this->getRelationType($polymorphicClassName, $caresRelation)
                        );
                    }

                    continue;
                }

                // Now that Polymorphic is out of the way, we can check for standard has_ones
                $caresRelation = $this->getRelationForClassName(
                    Config::forClass($careClassName)->get('has_many') ?? [],
                    $className,
                    $relation
                );

                // Yes, it was a has_many on the other end of the relationship. We can add this Edge and continue
                if ($caresRelation) {
                    $careNode = $this->findOrCreateNode($careClassName);
                    $edges[] = new Edge(
                        $careNode,
                        $node,
                        $caresRelation,
                        $this->getRelationType($careClassName, $caresRelation)
                    );

                    continue;
                }

                // The only remaining possibility is that this is a belongs_to on the other end of this relationship
                // (a has_one <-> has_one)
                $caresRelation = $this->getRelationForClassName(
                    Config::forClass($careClassName)->get('belongs_to') ?? [],
                    $className,
                    $relation
                );

                if (!$caresRelation) {
                    // The error we throw indicates that we're either missing a has_many or a belongs_to for this
                    // relationship, as having either of those would be valid for a has_one
                    $errors[] = sprintf(
                        'No valid has_many or belongs_to found between %s and %s for has_one relationship %s',
                        $careClassName,
                        $className,
                        $relation
                    );

                    continue;
                }

                $careNode = $this->findOrCreateNode($careClassName);
                $edges[] = new Edge(
                    $careNode,
                    $node,
                    $caresRelation,
                    $this->getRelationType($careClassName, $caresRelation)
                );
            }
        }

        if (count($errors) > 0) {
            throw new GraphBuildException($errors);
        }

        $cache->set(self::CACHE_KEY_EDGES, $edges);
        $this->edges = $cache->get(self::CACHE_KEY_EDGES);
    }

    private function buildGlobalCares(): void
    {
        $cache = Injector::inst()->get(self::CACHE_KEY);

        if ($cache->has(self::CACHE_KEY_GLOBAL_CARES)) {
            $this->global_cares = $cache->get(self::CACHE_KEY_GLOBAL_CARES);

            return;
        }

        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        $classes = array_map(
            static function ($c) {
                return ['className' => $c, 'cares' => Config::forClass($c)->get('global_cares')];
            },
            $classes
        );

        $classes = array_filter(
            $classes,
            static function ($c) {
                return is_array($c['cares']) && count($c['cares']) > 0;
            }
        );

        $classes = array_reduce(
            $classes,
            static function ($carry, $item) {
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

        $cache->set(self::CACHE_KEY_GLOBAL_CARES, $classes);
        $this->global_cares = $cache->get(self::CACHE_KEY_GLOBAL_CARES);
    }

    private function getThroughClassAndToField(array $throughData): ?array
    {
        $throughClass = $throughData['through'] ?? null;
        $throughFromField = $throughData['from'] ?? null;
        $throughToField = $throughData['to'] ?? null;

        // If no through class or field definitions are available, then we don't know what this is... but it isn't a
        // many_many through
        if (!$throughClass || !$throughFromField || !$throughToField) {
            return null;
        }

        // Our through class should have has_one relationships for our "from" and "to"
        $throughHasOnes = Config::forClass($throughClass)->get('has_one');

        // We're missing our "from" relationship, this means an invalid ORM setup
        if (!array_key_exists($throughFromField, $throughHasOnes)) {
            throw new Exception(sprintf(
                'Unable to find "from" has_one relationships %s on %s',
                $throughFromField,
                $throughClass
            ));
        }

        // We're missing our "to" relationship, this means an invalid ORM setup
        if (!array_key_exists($throughToField, $throughHasOnes)) {
            throw new Exception(sprintf(
                'Unable to find "to" has_one relationships %s on %s',
                $throughToField,
                $throughClass
            ));
        }

        return [
            $throughClass,
            $throughToField,
        ];
    }

    private function getCaresManyManyThroughEdge(Node $node, string $relation, array $careRelationData): ?Edge
    {
        // many_many through is a trip. We need to determine what the correct through model is, and then from there
        // we can find out what the ultimate "target" is. Then... we need to draw the line back to our original $node

        // Let's start by finding out what our through class is, and what to "to" and "from" relationships are
        [$throughClass, $throughToField] = $this->getThroughClassAndToField($careRelationData);
        $throughHasOnes = Config::forClass($throughClass)->get('has_one');

        // Ok, so we now have our "to" class, now we need to find the many_many relationship that is directed back
        // towards this through model
        [$toClass] = $this->getClassAndRelation($throughHasOnes[$throughToField]);
        $toClassConfig = Config::forClass($toClass);

        // We expect to find a corresponding many_many with a through defined
        $manyManyRelations = $toClassConfig->get('many_many') ?? [];
        $toRelation = null;

        foreach ($manyManyRelations as $manyManyField => $manyManyRelationData) {
            // We are expecting to find a many_many with a through definition. So we expect an array
            if (!is_array($manyManyRelationData)) {
                continue;
            }

            // We expect the "to" field on this side of the relationship to match our original "from" field
            if (($manyManyRelationData['from'] ?? null) !== $throughToField) {
                continue;
            }

            $toRelation = $manyManyField;
        }

        if (!$toRelation) {
            throw new Exception(sprintf(
                'No valid many_many (with "through" definition) found between %s and %s for relationship %s',
                $toClass,
                $node->getClassName(),
                $relation
            ));
        }

        return new Edge(
            $this->findOrCreateNode($toClass),
            $node,
            $toRelation,
            'many_many'
        );
    }

    private function getCaresManyManyEdge(Node $node, string $relation, string $careClassName): ?Edge
    {
        // Now that we know this is not a many_many through, we can start processing more straight forward relationship
        // definitions
        [$careClassName, $caresRelation] = $this->getClassAndRelation($careClassName);

        // A dot notation is available, so we can map this immediately and continue
        if ($caresRelation) {
            $careNode = $this->findOrCreateNode($careClassName);

            return new Edge(
                $careNode,
                $node,
                $caresRelation,
                $this->getRelationType($careClassName, $caresRelation)
            );
        }

        $careConfig = Config::forClass($careClassName);

        // No dot notation was available, so we need to figure out the relationship ourselves

        // When we are looking up reverse relationships for a many_many, they could be defined as either a many_many or
        // a belongs_many_many. Both are valid
        $many_many = $careConfig->get('many_many') ?? [];
        $belongs_many_many = $careConfig->get('belongs_many_many') ?? [];

        $caresRelation = $this->getRelationForClassName(
            array_merge($many_many, $belongs_many_many),
            $node->getClassName(),
            $relation
        );

        if (!$caresRelation) {
            throw new Exception(sprintf(
                'No valid many_many or belongs_many_many found between %s and %s for relationship %s',
                $careClassName,
                $node->getClassName(),
                $relation
            ));
        }

        $careNode = $this->findOrCreateNode($careClassName);

        return new Edge(
            $careNode,
            $node,
            $caresRelation,
            $this->getRelationType($careClassName, $caresRelation)
        );
    }

    private function getTouchesManyManyThroughEdge(Node $node, string $relation, array $careRelationData): ?Edge
    {
        // many_many through is a trip. We need to determine what the correct through model is, and then from there
        // we can find out what the ultimate "target" is

        // Let's start by finding out what our through class is, and what to "to" and "from" relationships are
        [$throughClass, $throughToField] = $this->getThroughClassAndToField($careRelationData);
        $throughHasOnes = Config::forClass($throughClass)->get('has_one');

        // Ok, so we now know what our "to" class is, so we can now build out our Edge "from" our origin class "to"
        // this $toClass
        [$toClass] = $this->getClassAndRelation($throughHasOnes[$throughToField]);

        return new Edge(
            $node,
            $this->findOrCreateNode($toClass),
            $relation,
            'many_many'
        );
    }
}
