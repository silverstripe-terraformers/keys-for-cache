<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;

/**
 * @property int $CaredBelongsToModelID
 * @property int $CaredHasOneModelID
 * @method CaredBelongsToModel CaredBelongsToModel()
 * @method CaredHasOneModel CaredHasOneModel()
 * @method HasManyList|CaredHasManyModel[] CaredHasManyModels()
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
    ];

    private static array $cares = [
        'CaredBelongsToModel',
        'CaredHasOneModel',
        'CaredHasManyModels',
    ];

    private static string $table_name = 'CaresPage';

    private static bool $has_cache_key = true;
}
