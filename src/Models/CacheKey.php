<?php

namespace Terraformers\KeysForCache\Models;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Maintain and manage cache keys for records
 *
 * @property string RecordClass
 * @property int RecordID
 * @property string KeyHash
 * @mixin Versioned
 */
class CacheKey extends DataObject
{

    private static string $table_name = 'CacheKey';

    private static array $db = [
        'KeyHash' => 'Varchar',
        'RecordClass' => 'Varchar',
        'RecordID' => 'Int',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    /**
     * Update the CacheKey if it is invalidated,
     * Create a CacheKey if it is empty
     */
    public static function updateOrCreateKey(string $recordClass, int $recordId): ?CacheKey
    {
        $cacheKey = static::findOrCreate($recordClass, $recordId);

        if (!$cacheKey) {
            return null;
        }

        $cacheKey->KeyHash = md5(implode('-', [$recordClass, $recordId, microtime()]));
        $cacheKey->write();

        return $cacheKey;
    }

    public static function findOrCreate(string $recordClass, int $recordId): ?CacheKey
    {
        $hasCacheKey = Config::forClass($recordClass)->get('has_cache_key');

        if (!$hasCacheKey) {
            return null;
        }

        $cacheKey = static::get()->filter([
            'RecordClass' => $recordClass,
            'RecordID' => $recordId,
        ])->first();

        if ($cacheKey === null || !$cacheKey->exists()) {
            $cacheKey = static::create();
            $cacheKey->RecordClass = $recordClass;
            $cacheKey->RecordID = $recordId;
            $cacheKey->KeyHash = md5(implode('-', [$recordClass, $recordId, microtime()]));
            $cacheKey->write();
        }

        return $cacheKey;
    }

    public static function remove(string $recordClass, int $recordId): void
    {
        $cacheKey = static::get()->filter([
            'RecordClass' => $recordClass,
            'RecordID' => $recordId,
        ])->first();

        if (!$cacheKey) {
            return;
        }

        $cacheKey->delete();
    }

    public function __toString(): string
    {
        return $this->KeyHash ?? 'no-hash';
    }
}
