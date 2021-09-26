<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasOneModel;

/**
 * @property int $CaredBelongsToModelFirstID
 * @property int $CaredBelongsToModelSecondID
 * @property int $CaredHasOneModelFirstID
 * @property int $CaredHasOneModelSecondID
 * @method DotNotationCaredBelongsToModel CaredBelongsToModelFirst()
 * @method DotNotationCaredBelongsToModel CaredBelongsToModelSecond()
 * @method DotNotationCaredHasOneModel CaredHasOneModelFirst()
 * @method DotNotationCaredHasOneModel CaredHasOneModelSecond()
 * @method HasManyList|DotNotationCaredHasManyModel[] CaredHasManyModelsFirst()
 * @method HasManyList|DotNotationCaredHasManyModel[] CaredHasManyModelsSecond()
 * @mixin CacheKeyExtension
 */
class DotNotationCaresPage extends Page implements TestOnly
{
    private static array $has_one = [
        'CaredBelongsToModelFirst' => DotNotationCaredBelongsToModel::class,
        'CaredBelongsToModelSecond' => DotNotationCaredBelongsToModel::class,
        'CaredHasOneModelFirst' => DotNotationCaredHasOneModel::class,
        'CaredHasOneModelSecond' => DotNotationCaredHasOneModel::class,
    ];

    private static array $has_many = [
        'CaredHasManyModelsFirst' => DotNotationCaredHasManyModel::class . '.CaresPageFirst',
        'CaredHasManyModelsSecond' => DotNotationCaredHasManyModel::class . '.CaresPageSecond',
    ];

    private static array $cares = [
        'CaredBelongsToModelFirst',
        'CaredBelongsToModelSecond',
        'CaredHasOneModelFirst',
        'CaredHasOneModelSecond',
        'CaredHasManyModelsFirst',
        'CaredHasManyModelsSecond',
    ];

    private static string $table_name = 'DotNotationCaresPage';

    private static bool $has_cache_key = true;
}
