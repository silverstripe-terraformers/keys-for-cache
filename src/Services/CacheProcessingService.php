<?php

namespace Terraformers\KeysForCache\Services;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\DataTransferObjects\EdgeUpdateDto;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Graph;

abstract class CacheProcessingService
{

    use Injectable;

    abstract protected function shouldPublishUpdates(): bool;

    public function processChange(DataObject $instance): void
    {
        // Can't process a record that hasn't been saved to the Database. This would only happen if a developer
        // specifically calls processChange() in their code. All module hooks for this method are triggered *after*
        // write() type events
        if (!$instance->isInDB()) {
            return;
        }

        $className = $instance->getClassName();

        // This record has already been processed in full. It is possible for multiple write() actions to be performed
        // on a single record through the publishing process
        if ($this->alreadyProcessed($className, $instance->ID)) {
            return;
        }

        $edgesToUpdate = $this->updateInstance($instance);

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

    private function updateEdge(EdgeUpdateDto $dto): array
    {
        $edge = $dto->getEdge();
        $instance = $dto->getInstance();
        $relationType = $edge->getRelationType();

        if ($relationType === 'has_one') {
            $idValue = $instance->getField($edge->getRelation().'ID');

            // A relationship field does exist here, but there is no relationship active
            if (!$idValue) {
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
        if ($relationType === 'belongs_to') {
            return $this->updateInstance($instance->{$edge->getRelation()}());
        }

        if ($relationType === 'has_many' || $relationType === 'many_many' || $relationType === 'belongs_many_many') {
            return $this->updateInstances($instance->{$edge->getRelation()}());
        }

        return [];
    }

    private function updateInstances(SS_List $instances): array
    {
        $results = [];

        foreach ($instances as $relatedInstance) {
            $results = array_merge(
                $results,
                $this->updateInstance($relatedInstance)
            );
        }

        return $results;
    }

    private function updateInstance(DataObject $instance): array
    {
        if (!$instance->isInDB()) {
            return [];
        }

        if ($this->alreadyProcessed($instance->ClassName, $instance->ID)) {
            return [];
        }

        // Find or create the CacheKey for this instance
        $cacheKey = CacheKey::updateOrCreateKey($instance);
        $processedUpdate = $this->getUpdatesService()->findOrCreateProcessedUpdate($instance->ClassName, $instance->ID);

        // If there is no CacheKey record representing this DataObject, then we can just create Edges here and return
        if (!$cacheKey) {
            return $this->createEdges($instance);
        }

        // We need to make sure that we are specifically writing this with reading mode set to DRAFT. If we write()
        // while a user is browsing in a LIVE reading mode, then this CacheKey will be "live" immediately
        // @see https://github.com/silverstripe/silverstripe-versioned/issues/382
        Versioned::withVersionedMode(static function () use ($cacheKey): void {
            Versioned::set_stage(Versioned::DRAFT);

            $cacheKey->write();
        });

        // Check to see if we need to publish this CacheKey
        if ($this->shouldPublishUpdates()) {
            if (CacheKey::config()->get('publish_recursive')) {
                $cacheKey->publishRecursive();
            } else {
                $cacheKey->publishSingle();
            }

            $processedUpdate->setPublished();
        }

        // Create and return the Edges for this DataObject
        return $this->createEdges($instance);
    }

    private function createEdges(DataObject $instance): array
    {
        $applicableEdges = $this->getGraph()->getEdgesFrom($instance->getClassName());

        if (count($applicableEdges) === 0) {
            return [];
        }

        return array_map(
            static function (Edge $e) use ($instance) {
                return new EdgeUpdateDto($e, $instance);
            },
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

        // We are in a "Draft" context, so we don't care whether the ProcessedUpdateDTO has been published or not. Its
        // existence means that it has been processed
        if (!$this->shouldPublishUpdates()) {
            return true;
        }

        // We are in a "Live" context, so we need to return whether this ProcessedUpdateDTO has been published
        return $processedUpdate->isPublished();
    }

    private function processGlobalCares(string $className): void
    {
        $globalCares = $this->getGraph()->getGlobalCares();
        $possibleClassNames = ClassInfo::ancestry($className);
        $cares = array_map(
            static function ($c) use ($globalCares) {
                return $globalCares[$c] ?? null;
            },
            $possibleClassNames,
        );
        $cares = array_filter(
            $cares,
            static function ($c) {
                return !is_null($c);
            }
        );
        $cares = array_merge(...array_values($cares));
        $cares = array_unique($cares);

        foreach ($cares as $careClass) {
            /** @var DataList|CacheKey[] $cacheKeys */
            $cacheKeys = Versioned::withVersionedMode(static function () use ($careClass): DataList {
                Versioned::set_stage(Versioned::DRAFT);

                return CacheKey::get()->filter('RecordClass', $careClass);
            });

            foreach ($cacheKeys as $cacheKey) {
                $cacheKey->doArchive();
            }
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
