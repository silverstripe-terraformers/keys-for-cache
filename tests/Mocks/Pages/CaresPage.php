<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThroughModel;

/**
 * @property int $CaredBelongsToModelID
 * @property int $CaredHasOneModelID
 * @method CaredBelongsToModel CaredBelongsToModel()
 * @method CaredHasOneModel CaredHasOneModel()
 * @method HasManyList|CaredHasManyModel[] CaredHasManyModels()
 * @method HasManyList|CaresPageCaredThroughModel[] CaresPageCaredThroughModels()
 * @method ManyManyList|CaredManyManyModel[] CaredManyManyModels()
 * @method ManyManyThroughList|CaredThroughModel[] CaredThroughModels()
 * @mixin CacheKeyExtension
 */
class CaresPage extends Page implements TestOnly
{
    private static array $has_one = [
        'CaredBelongsToModel' => CaredBelongsToModel::class,
        'CaredHasOneModel' => CaredHasOneModel::class,
    ];

    private static array $has_many = [
        'CaredHasManyModels' => CaredHasManyModel::class,
        'CaresPageCaredThroughModels' => CaresPageCaredThroughModel::class,
    ];

    private static array $many_many = [
        'CaredManyManyModels' => CaredManyManyModel::class,
        'CaredThroughModels' => [
            'through' => CaresPageCaredThroughModel::class,
            'from' => 'Parent',
            'to' => 'CaredThroughModel',
        ],
    ];

    private static array $cares = [
        'CaredBelongsToModel',
        'CaredHasOneModel',
        'CaredHasManyModels',
        'CaredManyManyModels',
        'CaredThroughModels',
    ];

    private static string $table_name = 'CaresPage';

    private static bool $has_cache_key = true;
}
