<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLDelete;
use Terraformers\KeysForCache\DataTransferObjects\EdgeUpdateDTO;
use Terraformers\KeysForCache\DataTransferObjects\ProcessedUpdateDTO;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\Models\CacheKey;
use Exception;

class CacheRelationService
{
    use Injectable;

    private Graph $graph;

    private array $processedUpdates;

    private array $globalCares;

    public function __construct()
    {
        $this->graph = Graph::build();
        $this->globalCares = $this->createGlobalCares();
        $this->processedUpdates = [];
    }

    public function getGraph(): Graph
    {
        return $this->graph;
    }

    public function processChange(DataObject $instance): void
    {
        $className = $instance->getClassName();
        $id = $instance->ID;
        CacheKey::updateOrCreateKey($className, $id);
        $this->processedUpdates[] = new ProcessedUpdateDTO($className, $id);
        $edgesToUpdate = $this->createEdges($instance);

        // Prevent edges from being used more than once
        $edgesUpdated = [];

        while (count($edgesToUpdate) > 0) {
            /** @var EdgeUpdateDTO $current */
            $current = array_pop($edgesToUpdate);
            $from = $current->getEdge()->getFromClassName();

            if (in_array($from, $edgesUpdated)) {
                continue;
            }

            $edgesToUpdate = array_merge(
                $edgesToUpdate,
                $this->updateEdge($current)
            );

            $edgesUpdated[] = $from;
        }

        $this->updateGlobalCares($className);
    }

    /**
     * Given a relation name, determine the relation type
     *
     * @param string $component Name of component
     * @return string has_one, has_many, many_many, belongs_many_many or belongs_to
     */
    private function getRelationType(string $className, string $relation): ?string
    {
        $types = ['has_one', 'has_many', 'many_many', 'belongs_many_many', 'belongs_to'];
        $config = Config::inst()->get($className);

        foreach ($types as $type) {
            $relations = $config->get($type);

            if ($relations && isset($relations[$relation])) {
                return $type;
            }
        }

        return null;
    }

    private function updateEdge(EdgeUpdateDTO $dto): array
    {
        $edge = $dto->getEdge();
        $instance = $dto->getInstance();
        $relation = $this->getRelationType($edge->getFromClassName(), $edge->getRelation());

        if (!$relation) {
            throw new Exception(sprintf(
                'No relationship field found for "%s" between "%s" and "%s"',
                $edge->getRelation(),
                $edge->getFromClassName(),
                $edge->getToClassName()
            ));
        }

        if ($relation === 'has_one') {
            if ($this->alreadyProcessed($instance->getField($edge->getRelation().'ID'),  $edge->getToClassName())) {
                return [];
            }

            $relatedInstance = $instance->getField($edge->getRelation());

            if (!$relatedInstance) {
                return [];
            }

            return $this->updateInstance($relatedInstance);
        }

        if ($relation === 'has_many') {
            return $this->updateInstances($instance->{$edge->getRelation()}(), $dto);
        }

        if ($relation === 'many_many') {
            // TODO: Handle this?
        }

        return [];
    }

    private function updateInstances(DataList $instances, EdgeUpdateDTO $dto): array
    {
        $results = [];

        foreach ($instances as $relatedInstance) {
            if ($this->alreadyProcessed($relatedInstance->ID,  $dto->getEdge()->getToClassName())) {
                continue;
            }

            $results = array_merge(
                $results,
                $this->updateInstance($relatedInstance)
            );
        }

        return $results;
    }

    private function updateInstance(DataObject $instance): array
    {
        $className = $instance->getClassName();
        $id = $instance->ID;
        CacheKey::updateOrCreateKey($className, $id);
        $this->processedUpdates[] = new ProcessedUpdateDTO($className, $id);

        return $this->createEdges($instance);
    }

    private function createEdges(DataObject $instance): array
    {
        $applicableEdges = $this->getGraph()->getEdges($instance->getClassName());

        if (count($applicableEdges) === 0) {
            return [];
        }

        return array_map(
            fn($e) => new EdgeUpdateDTO($e, $instance),
            $applicableEdges
        );
    }

    private function alreadyProcessed(int $id, string $getToClassName): bool
    {
        /** @var ProcessedUpdateDTO $processedUpdate */
        foreach ($this->processedUpdates as $processedUpdate) {
            $classNameMatches = $processedUpdate->getClassName() === $getToClassName;
            $idMatches = $processedUpdate->getId() === $id;

            if ($idMatches && $classNameMatches) {
                return true;
            }
        }

        return false;
    }

    public function updateGlobalCares(string $className): void
    {
        $cares = $this->getGlobalCares();
        $possibleClassNames = ClassInfo::ancestry($className);
        $cares = array_map(
            fn($c) => $cares[$c] ?? null,
            $possibleClassNames,
        );
        $cares = array_filter($cares, fn($c) => !is_null($c));
        $cares = array_merge(...array_values($cares));
        $cares = array_unique($cares);

        $cacheKeyTable = CacheKey::config()->get('table_name');

        foreach ($cares as $care) {
            SQLDelete::create(
                $cacheKeyTable,
                ['RecordClass' => $care]
            )->execute();
        }
    }

    public function getGlobalCares(): array
    {
        return $this->globalCares;
    }

    private function createGlobalCares(): array
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

        return $classes;
    }

    private function getClassesWithCacheKey(): array
    {
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        return array_filter(
            $classes,
            fn($c) => Config::forClass($c)->get('has_cache_key') === true
        );
    }
}
