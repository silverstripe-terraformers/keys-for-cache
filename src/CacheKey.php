<?php

namespace Terraformers\KeysForCache;

use SilverStripe\ORM\DataObject;

/**
 * Maintain and manage cache keys for records
 *
 * @property string RecordClass
 * @property int RecordID
 * @property string KeyHash
 */
class CacheKey extends DataObject
{

    private static string $table_name = 'CacheKey';

    private static array $db = [
        'KeyHash' => 'Varchar',
    ];

    private static array $belongs_to = [
        'Record' => DataObject::class,
    ];

    /**
     * Update the CacheKey if it is invalidated,
     * Create a CacheKey if it is empty
     */
    public static function updateOrCreateKey(string $recordClass, int $recordId): CacheKey
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

        return $cacheKey;
    }

    public function __toString(): string
    {
        return $this->KeyHash ?? 'no-hash';
    }
}
