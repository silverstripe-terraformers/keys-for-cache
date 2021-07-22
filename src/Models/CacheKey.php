<?php

namespace Terraformers\KeysForCache\Models;

use SilverStripe\ORM\DataObject;

/**
 * Maintain and manage cache keys for records
 *
 * @property string $RecordClass
 * @property int $RecordID
 * @property string $KeyHash
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

    /**
     * Update the CacheKey if it is invalivated,
     * Create a CacheKey if it is empty
     *
     * @param string $recordClass
     * @param int $recordId
     */
    public static function updateOrCreateKey(string $recordClass, int $recordId): void
    {
        $cacheKey = static::get()->filter([
            'RecordClass' => $recordClass,
            'RecordID' => $recordId,
        ])->first();

        if ($cacheKey === null || !$cacheKey->exists()) {
            $cacheKey = static::create();
            $cacheKey->RecordClass = $recordClass;
            $cacheKey->RecordID = $recordId;
        }

        $cacheKey->KeyHash = md5(implode('-', [$recordClass, $recordId, microtime()]));
        $cacheKey->write();
    }

}
