<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectSchema;

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

            if (!$ownedByRelationships) {
                continue;
            }

            if (!$hasOneRelationships) {
                continue;
            }

            $table = Config::inst()->get($ancestorClassName, 'table_name');

            foreach ($hasOneRelationships as $relationshipName => $relationshipClassName) {
                if (!in_array($relationshipName, $ownedByRelationships)) {
                    continue;
                }

                // Strip out any field relationship and just keep the classname
                $relationshipClassName = strtok($relationshipClassName, '.');
                $fieldName = $relationshipName . 'ID';

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

    public static function getOwnedByHasMany(string $className): array
    {
        $relationships = [];
        $tableQueries = [];

        $ownedByRelationships = Config::inst()->get($className, 'owned_by', 1);
        $hasManyRelationships = Config::inst()->get($className, 'has_many', 1);

        if (!$ownedByRelationships) {
            return [];
        }

        if (!$hasManyRelationships) {
            return [];
        }

        foreach ($hasManyRelationships as $relationship => $relationshipClassName) {
            if (!in_array($relationship, $ownedByRelationships)) {
                continue;
            }

            // Strip out any field relationship and just keep the classname
            $relationshipArray = explode('.', $relationshipClassName);
            $relationshipClassName = array_shift($relationshipArray);
            $relationshipFieldName = array_shift($relationshipArray);

            $ownerTableName = DataObject::singleton($relationshipClassName)->config()->uninherited('table_name');

            if (!$ownerTableName) {
                $separator = DataObjectSchema::config()->uninherited('table_namespace_separator');
                $ownerTableName = str_replace('\\', $separator, trim($relationshipClassName, '\\'));
            }

            if ($relationshipFieldName === null) {
                $ownerHasOnes = Config::inst()->get($relationshipClassName, 'has_one');

                if (!$ownerHasOnes) {
                    continue;
                }

                foreach ($ownerHasOnes as $fieldName => $childClassName) {
                    $childClassName = strtok($childClassName, '.');

                    if ($className === $childClassName) {
                        $relationshipFieldName = $fieldName . 'ID';

                        break;
                    }
                }
            }

            if (!$relationshipFieldName) {
                continue;
            }

            if (!array_key_exists($ownerTableName, $tableQueries)) {
                $tableQueries[$ownerTableName] = [
                    'ClassName' => $relationshipClassName,
                    'FieldNames' => [],
                ];
            }

            $tableQueries[$ownerTableName]['FieldNames'][] = $relationshipFieldName;
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
