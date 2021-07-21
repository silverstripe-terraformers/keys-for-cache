<?php

namespace Terraformers\KeysForCache;

use SilverStripe\Core\Config\Config;

class CacheDependent
{
    /**
     * Get the cache dependent classes of the given class
     *
     * @param string $className
     * @return array
     */
    public static function getForClass(string $className): array
    {
        $dependents = [];

        $cacheDependencies = self::getCacheDependencies();

        foreach ($cacheDependencies as $dependent => $dependency) {
            if (in_array($className,  $dependency)) {
                $dependents[] = $dependent;
            }
        }

        return $dependents;
    }

    /**
     * Get cache dependencies for all classes
     *
     * @return array
     */
    public static function getCacheDependencies(): array
    {
        // all configs
        $configs = Config::inst()->getAll();

        $cacheDependencies = [];

        foreach ($configs as $className => $config) {
            if (array_key_exists('cache_dependencies', $config)) {
                $cacheDependencies[$className] = $config['cache_dependencies'];
            }
        }

        return $cacheDependencies;
    }
}
