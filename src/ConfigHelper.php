<?php

namespace Terraformers\KeysForCache;

use App\Models\MenuGroup;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DataObject;

class ConfigHelper
{
    /**
     * Get the config dependent classes of the given class
     *
     * @param string $className
     * @return array
     */
    public static function getGlobalCacheDependencies(string $className, string $configName): array
    {
        $dependents = [];

        $allConfigs = self::getAllConfigsForName($configName);

        foreach ($allConfigs as $owner => $dependency) {
            if (in_array($className,  $dependency)) {
                $dependents[] = $owner;
            }
        }

        return $dependents;
    }

    public static function getOwnedByHasOnes(string $className): array
    {
        $ownedByRelationships = Config::inst()->get($className, 'owned_by', 1);
        $hasOneRelationships = Config::inst()->get($className, 'has_one', 1);
        $relationships = [];

        foreach ($hasOneRelationships as $relationship => $relationshipClassName) {
            // Strip out any field relationship and just keep the classname
            $relationshipClassName = strtok($relationshipClassName, '.');
        }

        $hasManyRelationships = Config::inst()->get($className, 'has_many', 1);

        // TODO Add support for many_many

        Debug::dump($ownedByRelationships);
        Debug::dump($hasOneRelationships);
        Debug::dump($hasManyRelationships);

        return [];
    }

    /**
     * Get specific configs for all classes
     *
     * @return array [Page:class => [File:class, Image:class]]
     */
    public static function getAllConfigsForName(string $configName): array
    {
        $specificConfigs = [];

        foreach (ClassInfo::subclassesFor(DataObject::class) as $className) {
            $config = Config::inst()->get($className, $configName, 1);

            if (!$config) {
                continue;
            }

            $specificConfigs[$className] = $config;
        }

        return $specificConfigs;
    }
}
