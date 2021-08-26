<?php

namespace Terraformers\KeysForCache\Models;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataList;
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

    public function __toString(): string
    {
        return $this->KeyHash ?? 'no-hash';
    }

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

        $cacheKey->KeyHash = static::generateKeyHash($recordClass, $recordId);
        $cacheKey->write();

        return $cacheKey;
    }

    public static function findOrCreate(string $recordClass, int $recordId): ?CacheKey
    {
        $hasCacheKey = Config::forClass($recordClass)->get('has_cache_key');

        if (!$hasCacheKey) {
            return null;
        }

        // We need to ensure that we fetch our CacheKey with our reading mode set to DRAFT. During a publish event, our
        // reading mode is LIVE, which would mean that we won't ever find a DRAFT only CacheKey that matches our
        // criteria
        $cacheKey = Versioned::withVersionedMode(static function () use ($recordClass, $recordId): ?CacheKey {
            Versioned::set_stage(Versioned::DRAFT);

            return static::get()->filter([
                'RecordClass' => $recordClass,
                'RecordID' => $recordId,
            ])->first();
        });

        if ($cacheKey === null || !$cacheKey->exists()) {
            $cacheKey = static::create();
            $cacheKey->RecordClass = $recordClass;
            $cacheKey->RecordID = $recordId;
            $cacheKey->KeyHash = static::generateKeyHash($recordClass, $recordId);
            $cacheKey->write();
        }

        return $cacheKey;
    }

    public static function remove(string $recordClass, int $recordId): void
    {
        // There is a non-zero chance that we could have multiple CacheKeys for a single record. If everything always
        // worked perfectly then it shouldn't happen, but from a data consistency point of view, it is possible. This
        // is our opportunity to clean it up
        /** @var DataList|CacheKey[] $cacheKeys */
        $cacheKeys = static::get()->filter([
            'RecordClass' => $recordClass,
            'RecordID' => $recordId,
        ]);

        if ($cacheKeys->count() === 0) {
            return;
        }

        foreach ($cacheKeys as $cacheKey) {
            $cacheKey->doArchive();
        }
    }

    public static function generateKeyHash(string $recordClass, int $recordId): string
    {
        return implode('-', [$recordClass, $recordId, microtime()]);
    }
}
