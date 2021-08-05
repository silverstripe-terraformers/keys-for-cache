<?php

namespace Terraformers\KeysForCache;

use App\Taxonomy\Relations\RelatedContentBlockTaxonomyTerm;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

class CacheRelationService
{
    use Injectable;

    private Graph $graph;

    private array $processedUpdates;

    public function __construct()
    {
        $this->graph = Graph::build();
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

        // TODO: Handle global cares
    }

    private function updateEdge(EdgeUpdateDTO $dto): array
    {
        $edge = $dto->getEdge();
        $instance = $dto->getInstance();
        $relation = $instance->getRelationType($edge->getRelation());

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

        if (!$relation) {
            $relatedInstances = DataObject::get($edge->getToClassName())
                ->filter($edge->getRelation().'ID', $instance->ID);

            return $this->updateInstances($relatedInstances, $dto);
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

    private function getClassesWithCacheKey(): array
    {
        $classes = ClassInfo::getValidSubClasses(DataObject::class);

        return array_filter(
            $classes,
            fn($c) => Config::forClass($c)->get('has_cache_key') === true
        );
    }
}