<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Relation\TouchesPageTouchedThroughModel;

/**
 * @property int $TouchedBelongsToModelID
 * @property int $TouchedHasOneModelID
 * @method TouchedBelongsToModel TouchedBelongsToModel()
 * @method TouchedHasOneModel TouchedHasOneModel()
 * @method HasManyList|TouchedHasManyModel[] TouchedHasManyModels()
 * @method ManyManyList|TouchedManyManyModel[] TouchedManyManyModels()
 * @method ManyManyThroughList|TouchedManyManyModel[] TouchedThroughModels()
 * @mixin CacheKeyExtension
 */
class TouchesPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchedBelongsToModel' => TouchedBelongsToModel::class,
        'TouchedHasOneModel' => TouchedHasOneModel::class,
    ];

    private static array $has_many = [
        'TouchedHasManyModels' => TouchedHasManyModel::class,
    ];

    private static array $many_many = [
        'TouchedManyManyModels' => TouchedManyManyModel::class,
        'TouchedThroughModels' => [
            'through' => TouchesPageTouchedThroughModel::class,
            'from' => 'Parent',
            'to' => 'TouchedThroughModel',
        ],
    ];

    private static array $touches = [
        'TouchedBelongsToModel',
        'TouchedHasOneModel',
        'TouchedHasManyModels',
        'TouchedManyManyModels',
        'TouchedThroughModels',
    ];

    private static string $table_name = 'TouchesPage';

    private static bool $has_cache_key = false;
}
