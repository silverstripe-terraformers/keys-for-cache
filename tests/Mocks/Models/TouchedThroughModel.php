<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Relation\TouchesPageTouchedThroughModel;

/**
 * @property string $Title
 * @method ManyManyList|CaresPage CaresPages()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class TouchedThroughModel extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $many_many = [
        'TouchesPages' => [
            'through' => TouchesPageTouchedThroughModel::class,
            'from' => 'TouchedThroughModel',
            'to' => 'Parent',
        ],
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'TouchedThroughModel';

    private static bool $has_cache_key = true;
}
