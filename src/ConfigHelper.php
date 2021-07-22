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
        $relationships = [];
        $tableQueries = [];

        foreach (ClassInfo::ancestry($className) as $ancestorClassName) {
            $ownedByRelationships = Config::inst()->get($ancestorClassName, 'owned_by', 1);
            $hasOneRelationships = Config::inst()->get($ancestorClassName, 'has_one', 1);
            $table = Config::inst()->get($ancestorClassName, 'table_name');

            if (!$ownedByRelationships) {
                continue;
            }

            if (!$hasOneRelationships) {
                continue;
            }

            foreach ($hasOneRelationships as $relationship => $relationshipClassName) {
                // Strip out any field relationship and just keep the classname
                $relationshipClassName = strtok($relationshipClassName, '.');
                $fieldName = $relationship . 'ID';

                if (in_array($fieldName, $relationships)) {
                    continue;
                }

                if (!array_key_exists($table, $tableQueries)) {
                    $tableQueries[$table] = [];
                }

                $tableQueries[$table] = [
                    'FieldName' => $fieldName,
                    'RelationshipClassName' => $relationshipClassName,
                ];
            }
        }

        return $tableQueries;
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
