<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOneModel;

/**
 * @property int $TouchedBelongsToModelID
 * @property int $TouchedHasOneModelID
 * @method TouchedBelongsToModel TouchedBelongsToModel()
 * @method TouchedHasOneModel TouchedHasOneModel()
 * @method HasManyList|TouchedHasManyModel[] TouchedHasManyModels()
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

    private static array $touches = [
        'TouchedBelongsToModel',
        'TouchedHasOneModel',
        'TouchedHasManyModels',
    ];

    private static string $table_name = 'TouchesPage';

    private static bool $has_cache_key = false;
}
