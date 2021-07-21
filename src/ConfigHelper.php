<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\Config\Config;

class ConfigHelper
{
    /**
     * Get the config dependent classes of the given class
     *
     * @param string $className
     * @return array
     */
    public static function getConfigDependents(string $className, string $configName): array
    {
        $dependents = [];

        $configs = self::getConfigForName($configName);

        foreach ($configs as $dependent => $dependency) {
            if (in_array($className,  $dependency)) {
                $dependents[] = $dependent;
            }
        }

        return $dependents;
    }

    /**
     * Get specific configs for all classes
     *
     * @return array [Page:class => [File:class, Image:class]]
     */
    public static function getConfigForName(string $configName): array
    {
        // all configs
        $configs = Config::inst()->getAll();

        $specificConfigs = [];

        foreach ($configs as $className => $config) {
            if (array_key_exists($configName, $config)) {
                $specificConfigs[$className] = $config[$configName];
            }
        }

        return $specificConfigs;
    }
}
