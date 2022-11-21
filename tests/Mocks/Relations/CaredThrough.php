<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Relations;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * @property string $Title
 * @method ManyManyList|CaresPage CaresPages()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class CaredThrough extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $many_many = [
        'CaredPages' => [
            'through' => CaresPageCaredThrough::class,
            'from' => 'CaredThrough',
            'to' => 'Parent',
        ],
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'CaredThrough';

    private static bool $has_cache_key = false;
}
