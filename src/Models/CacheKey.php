<?php

namespace Terraformers\KeysForCache\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * Maintain and manage cache keys for records
 *
 * @property string $RecordClass
 * @property int $RecordID
 * @property string $KeyHash
 * @method DataObject|CacheKeyExtension Record()
 * @mixin Versioned
 */
class CacheKey extends DataObject
{
    private static string $table_name = 'CacheKey';

    private static array $db = [
        'KeyHash' => 'Varchar',
    ];

    private static array $has_one = [
        'Record' => DataObject::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    /**
     * Update the CacheKey record for a given instance if a CacheKey record exists,
     * Create a CacheKey record for a given instance if it doesn't have a CacheKey record yet
     *
     * @param DataObject $dataObject
     * @return CacheKey|null
     */
    public static function updateOrCreateKey(DataObject $dataObject): ?CacheKey
    {
        $cacheKey = static::findOrCreate($dataObject);

        if (!$cacheKey) {
            return null;
        }

        $cacheKey->KeyHash = static::generateKeyHash($dataObject);
        $cacheKey->write();

        return $cacheKey;
    }

    /**
     * Find the CacheKey record for a given instance if it has one
     * Or create an CacheKey record for it if it doesn't have one
     *
     * @param DataObject|CacheKeyExtension $dataObject
     * @return CacheKey|null
     */
    public static function findOrCreate(DataObject $dataObject): ?CacheKey
    {
        $hasCacheKey = $dataObject->config()->get('has_cache_key');

        if (!$hasCacheKey) {
            return null;
        }

        // We need to ensure that we fetch our CacheKey with our reading mode set to DRAFT. During a publish event, our
        // reading mode is LIVE, which would mean that we won't ever find a DRAFT only CacheKey that matches our
        // criteria
        $cacheKey = Versioned::withVersionedMode(static function () use ($dataObject): ?CacheKey {
            Versioned::set_stage(Versioned::DRAFT);

            return static::get()->filter([
                'RecordClass' => $dataObject->ClassName,
                'RecordID' => $dataObject->ID,
            ])->first();
        });

        if ($cacheKey === null || !$cacheKey->exists()) {
            $cacheKey = static::create();
            $cacheKey->RecordClass = $dataObject->ClassName;
            $cacheKey->RecordID = $dataObject->ID;
            $cacheKey->KeyHash = static::generateKeyHash($dataObject);
            $cacheKey->write();
        }

        return $cacheKey;
    }

    /**
     * Remove Cache Key record(s) of a given instance
     *
     * @param DataObject|CacheKeyExtension $dataObject
     */
    public static function remove(DataObject $dataObject): void
    {
        // There is a non-zero chance that we could have multiple CacheKeys for a single record. If everything always
        // worked perfectly then it shouldn't happen, but from a data consistency point of view, it is possible. This
        // is our opportunity to clean it up
        foreach ($dataObject->CacheKeys() as $cacheKey) {
            $cacheKey->doArchive();
        }
    }

    /**
     * Generate KeyHash for an instance
     *
     * @param DataObject $dataObject the instance that we generate KeyHash for
     * @return string KeyHash value
     */
    protected static function generateKeyHash(DataObject $dataObject): string
    {
        // getUniqueKey() has only been around since 4.7, but ideally this is what we would like to use as the base for
        // our KeyHash
        $uniqueKey = $dataObject->hasMethod('getUniqueKey')
            ? $dataObject->getUniqueKey()
            : sprintf('%s-%s', $dataObject->ClassName, $dataObject->ID);

        $dataObject->invokeWithExtensions('updateGenerateKeyHash', $uniqueKey);

        return implode('-', [$uniqueKey, microtime(true)]);
    }
}
