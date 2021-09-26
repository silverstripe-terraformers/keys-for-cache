<?php

namespace Terraformers\KeysForCache\Services;

use Exception;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLDelete;
use Terraformers\KeysForCache\DataTransferObjects\EdgeUpdateDto;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Graph;

abstract class CacheProcessingService
{
    use Injectable;

    abstract protected function shouldPublishUpdates(): bool;

    public function processChange(DataObject $instance): void
    {
        $className = $instance->getClassName();

        // This record has already been processed in full. It is possible for multiple write() actions to be performed
        // on a single record through the publishing process
        if ($this->alreadyProcessed($className, $instance->ID)) {
            return;
        }

        $this->updateInstance($instance);
        $edgesToUpdate = $this->createEdges($instance);

        while (count($edgesToUpdate) > 0) {
            /** @var EdgeUpdateDto $current */
            $current = array_pop($edgesToUpdate);

            $edgesToUpdate = array_merge(
                $edgesToUpdate,
                $this->updateEdge($current)
            );
        }

        $this->processGlobalCares($className);
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

        foreach ($types as $type) {
            $relations = Config::inst()->get($className, $type);

            if ($relations && isset($relations[$relation])) {
                return $type;
            }
        }

        return null;
    }

    private function updateEdge(EdgeUpdateDto $dto): array
    {
        $edge = $dto->getEdge();
        $instance = $dto->getInstance();
        $relation = $this->getRelationType($edge->getFromClassName(), $edge->getRelation());

        // No relationship exists for this From class
        if (!$relation) {
            // Check to see whether it is a has_one relationship in the other direction
            $relation = $this->getRelationType($edge->getToClassName(), $edge->getRelation());

            // If we still can't find a relationship, then we're in an error state
            if (!$relation) {
                throw new Exception(sprintf(
                    'No relationship field found for "%s" between "%s" and "%s"',
                    $edge->getRelation(),
                    $edge->getFromClassName(),
                    $edge->getToClassName()
                ));
            }

            $relatedInstances = DataObject::get($edge->getToClassName())
                ->filter($edge->getRelation().'ID', $instance->ID);

            return $this->updateInstances($relatedInstances, $dto);
        }

        if ($relation === 'has_one') {
            $idValue = $instance->getField($edge->getRelation().'ID');

            // A relationship field does exist here, but there is no relationship active
            if (!$idValue) {
                return [];
            }

            if ($this->alreadyProcessed($edge->getToClassName(), $instance->getField($edge->getRelation().'ID'))) {
                return [];
            }

            $relatedInstance = $instance->getField($edge->getRelation());

            if (!$relatedInstance) {
                return [];
            }

            return $this->updateInstance($relatedInstance);
        }

        // belongs_to is a has_one <-> has_one, however, there is no ID field present here. Instead we just need to call
        // the method that the ORM provides
        if ($relation === 'belongs_to') {
            return $this->updateInstance($instance->{$edge->getRelation()}());
        }

        if ($relation === 'has_many') {
            return $this->updateInstances($instance->{$edge->getRelation()}(), $dto);
        }

        if ($relation === 'many_many') {
            // TODO: Handle this?
        }

        return [];
    }

    private function updateInstances(DataList $instances, EdgeUpdateDto $dto): array
    {
        $results = [];

        foreach ($instances as $relatedInstance) {
            if ($this->alreadyProcessed($dto->getEdge()->getToClassName(), $relatedInstance->ID)) {
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
        // Find or create the CacheKey for this instance
        $cacheKey = CacheKey::updateOrCreateKey($instance);
        $processedUpdate = $this->getUpdatesService()->findOrCreateProcessedUpdate($instance->ClassName, $instance->ID);

        if ($cacheKey) {
            // Check to see if we need to publish this CacheKey
            if ($this->shouldPublishUpdates()) {
                $cacheKey->publishRecursive();
                $processedUpdate->setPublished();
            }
        }

        return $this->createEdges($instance);
    }

    private function createEdges(DataObject $instance): array
    {
        $applicableEdges = $this->getGraph()->getEdges($instance->getClassName());

        if (count($applicableEdges) === 0) {
            return [];
        }

        return array_map(
            fn($e) => new EdgeUpdateDto($e, $instance),
            $applicableEdges
        );
    }

    private function alreadyProcessed(string $className, int $id): bool
    {
        $processedUpdate = $this->getUpdatesService()->findProcessedUpdate($className, $id);

        // No ProcessedUpdateDTO exists, so no, this has not been processed
        if (!$processedUpdate) {
            return false;
        }

        // We are in a "Draft" context, so we don't care whether or not the ProcessedUpdateDTO has been published or
        // not. Its existence means that it has been processed
        if (!$this->shouldPublishUpdates()) {
            return true;
        }

        // We are in a "Live" context, so we need to return whether or not this ProcessedUpdateDTO has been published
        return $processedUpdate->isPublished();
    }

    private function processGlobalCares(string $className): void
    {
        $cares = $this->getGraph()->getGlobalCares();
        $possibleClassNames = ClassInfo::ancestry($className);
        $cares = array_map(
            fn($c) => $cares[$c] ?? null,
            $possibleClassNames,
        );
        $cares = array_filter($cares, fn($c) => !is_null($c));
        $cares = array_merge(...array_values($cares));
        $cares = array_unique($cares);

        $cacheKeyTable = CacheKey::config()->get('table_name');

        foreach ($cares as $careClass) {
            SQLDelete::create(
                $cacheKeyTable,
                ['RecordClass' => $careClass]
            )->execute();
        }
    }

    private function getGraph(): Graph
    {
        return Graph::singleton();
    }

    private function getUpdatesService(): ProcessedUpdatesService
    {
        return ProcessedUpdatesService::singleton();
    }
}
