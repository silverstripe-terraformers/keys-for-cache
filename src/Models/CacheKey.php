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
     * @param DataObject $record
     */
    public static function updateOrCreateKey(DataObject $record): void
    {
        $cacheKey = static::get()->filter([
            'RecordClass' => $record->ClassName,
            'RecordID' => $record->ID,
        ])->first();

        if ($cacheKey === null || !$cacheKey->exists()) {
            $cacheKey = static::create();
            $cacheKey->RecordClass = $record->ClassName;
            $cacheKey->RecordID = $record->ID;
        }

        $cacheKey->KeyHash = md5(implode('-', [$record->ClassName, $record->ID, microtime()]));
        $cacheKey->write();
    }

}
