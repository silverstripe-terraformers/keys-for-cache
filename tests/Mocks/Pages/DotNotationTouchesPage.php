<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasOneModel;

/**
 * @property int $TouchedBelongsToModelFirstID
 * @property int $TouchedBelongsToModelSecondID
 * @property int $TouchedHasOneModelFirstID
 * @property int $TouchedHasOneModelSecondID
 * @method DotNotationTouchedBelongsToModel TouchedBelongsToModelFirst()
 * @method DotNotationTouchedBelongsToModel TouchedBelongsToModelSecond()
 * @method DotNotationTouchedHasOneModel TouchedHasOneModelFirst()
 * @method DotNotationTouchedHasOneModel TouchedHasOneModelSecond()
 * @method HasManyList|DotNotationTouchedHasManyModel[] TouchedHasManyModelsFirst()
 * @method HasManyList|DotNotationTouchedHasManyModel[] TouchedHasManyModelsSecond()
 * @mixin CacheKeyExtension
 */
class DotNotationTouchesPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchedBelongsToModelFirst' => DotNotationTouchedBelongsToModel::class,
        'TouchedBelongsToModelSecond' => DotNotationTouchedBelongsToModel::class,
        'TouchedHasOneModelFirst' => DotNotationTouchedHasOneModel::class,
        'TouchedHasOneModelSecond' => DotNotationTouchedHasOneModel::class,
    ];

    private static array $has_many = [
        'TouchedHasManyModelsFirst' => DotNotationTouchedHasManyModel::class . '.TouchesPageFirst',
        'TouchedHasManyModelsSecond' => DotNotationTouchedHasManyModel::class . '.TouchesPageSecond',
    ];

    private static array $touches = [
        'TouchedBelongsToModelFirst',
        'TouchedBelongsToModelSecond',
        'TouchedHasOneModelFirst',
        'TouchedHasOneModelSecond',
        'TouchedHasManyModelsFirst',
        'TouchedHasManyModelsSecond',
    ];

    private static string $table_name = 'DotNotationTouchesPage';

    private static bool $has_cache_key = false;
}
