<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;

class CacheRelationService
{
    use Injectable;

    public function getTree(): array
    {
        $classes = $this->getClassesWithCacheKey();

        return $classes;

        $relations = new RelationDTO();

        foreach ($classes as $className) {
            $relations->setRelation($className, $this->getRelationsForClass($className));
        }

        return $this->fillRelations($relations);
    }

    /*
     * Recursively create a relation mapping config
     */
    public function fillRelations(RelationDTO $relations): RelationDTO
    {
        $foundANewRelation = false;

        foreach ($relations->getRelations() as $className => $classRelations) {
            // We now need to know if any of these class relations have
            // relations that are not already added to the relation for $className

            // therefore for each relation, we need to get all of it's relations
            foreach ($classRelations as $classRelation) {
                $foundANewRelation = $this->combineRelations($relations, $className, $this->getRelationsForClass($classRelation)) || $foundANewRelation;
            }
        }

        return $foundANewRelation
            ? $this->fillRelations($relations)
            : $relations;
    }

    public function combineRelations(RelationDTO $relations, $className, $relatedClasses): bool
    {
        $newClasses = [];
        foreach ($relatedClasses as $relatedClass) {
            if ($relations->hasRelationForClass($className, $relatedClass)) {
                continue;
            }

            $newClasses[] = $relatedClass;
        }

        if (count($newClasses) === 0) {
            return false;
        }

        $relations->setRelation($className, array_merge(
            $relations->getRelationsForClass($className),
            $newClasses,
        ));

        return true;
    }

    public function getRelationsForClass(string $className): array
    {
        $config = Config::forClass($className);
        $types = ['has_one', 'has_many', 'many_many', 'belongs_many_many', 'belongs_to'];
        $relations = [];

        foreach ($types as $type) {
            $relations = array_merge($relations, $this->getFromConfig($config, $type));
        }

        return $relations;
    }

    public function getFromConfig(Config_ForClass $config, string $relation): array
    {
        $relations = (array)$config->get($relation);

        // Handle many many through relations
        if ($relation === 'many_many') {
            $relations = array_map(function ($item): string {
                return is_array($item) ? $item['through'] : $item;
            }, $relations);
        }

        // Strip out the `.Relation` part of classnames is they exist
        $relations = $relations && count($relations) > 0
            ? preg_replace('/(.+)?\..+/', '$1', $relations)
            : [];

        $relations = IgnoredClasses::singleton()->filter($relations);

        return $relations;
    }

    public function getRelationConfig(): Graph
    {
        $graph = new Graph();

        $this->buildGraph($graph);

        return $graph;
    }

    public function buildGraph(Graph $graph): void
    {
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
    }

    private function getClassesWithCacheKey(): array
    {
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        // Only add classes we care about generating keys for
        $classes = array_filter($classes, function (string $className): bool {
            return Config::forClass($className)->get('has_cache_key') === true;
        });

        return $classes;
    }
}
