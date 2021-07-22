<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Terraformers\KeysForCache\Events\CacheEvent;
use Terraformers\KeysForCache\Events\DependencyEvent;
use Terraformers\KeysForCache\Models\CacheKey;

class EventManager
{
    public const EVENT_CACHE_EVENT = 'event.send.cache';
    public const EVENT_DEPENDENCY_EVENT = 'event.send.dependency';

    use Injectable;

    private ?EventDispatcher $dispatcher;

    private array $cacheEvents = [];

    private array $dependencyEvents = [];

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();

        self::boot();
    }

    public function flushCache(): void
    {
        $this->cacheEvents = [];
        $this->dependencyEvents = [];
    }

    public function handleCacheEvent(string $recordClass, int $recordId): void
    {
        $key = sprintf('%s-%s', $recordClass, $recordId);

        if (in_array($key, $this->cacheEvents)) {
            return;
        }

        $this->cacheEvents[] = $key;
        $this->getDispatcher()->dispatch(new CacheEvent($recordClass, $recordId), static::EVENT_CACHE_EVENT);
    }

    public function handleDependencyEvent(string $recordClass): void
    {
        if (in_array($recordClass, $this->dependencyEvents)) {
            return;
        }

        $this->dependencyEvents[] = $recordClass;
        $this->getDispatcher()->dispatch(new DependencyEvent($recordClass), static::EVENT_DEPENDENCY_EVENT);
    }

    public function getDispatcher(): ?EventDispatcher
    {
        return $this->dispatcher;
    }

    // Boots up the subscribersd
    public function boot(): void
    {
        $this->getDispatcher()->addListener(static::EVENT_DEPENDENCY_EVENT, function(DependencyEvent $event) {
            $recordIds = DataObject::get($event->getClassName())->column();

            foreach ($recordIds as $recordId) {
                EventManager::singleton()->handleCacheEvent($event->getClassName(), $recordId);
            }
        });

        $this->getDispatcher()->addListener(static::EVENT_CACHE_EVENT, function(CacheEvent $event) {
            $shouldUpdateSelf = Config::inst()->get($event->getClassName(), 'has_cache_key');

            if ($shouldUpdateSelf) {
                CacheKey::updateOrCreateKey($event->getClassName(), $event->getId());
            }

            $cacheDependents = ConfigHelper::getGlobalCacheDependencies($event->getClassName(), 'cache_dependencies');

            foreach ($cacheDependents as $dependent) {
                EventManager::singleton()->handleDependencyEvent($dependent);
            }

            $hasOneDependencies = ConfigHelper::getOwnedByHasOnes($event->getClassName());

            foreach ($hasOneDependencies as $table => $dependency) {
                Debug::dump($table);
                Debug::dump($dependency);

                $sql = SQLSelect::create($dependency['FieldName'], $table, ['ID' => $event->getId()]);
                Debug::dump($sql->sql());
                $result = $sql->execute()->column($dependency['FieldName']);

                if (!$result) {
                    continue;
                }

                Debug::dump($result);
            }

//            $dispatcher = static::singleton()->getDispatcher();
//
//            $onwers = ConfigHelper::getConfigDependents($event->getClassName(), 'owns');;
//
//            foreach ($thingsThatOwnThis as $ownThi) {
//                $items = DataObject::get($ownThi)
//                    ->filter('Relation', $event->getId())
//                    ->map('ClassName', 'ID')
//                    ->toArray();
//
//                foreach ($items as $className => $id) {
//                    $dispatcher->dispatch(
//                        new CacheEvent($className, $id),
//                        sprintf(CacheEvent::EVENT_NAME, $className)
//                    );
//                }
//            }
        });
    }
}
