<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationCaredHasOne;

/**
 * @property int $CaredBelongsToFirstID
 * @property int $CaredBelongsToSecondID
 * @property int $CaredHasOneFirstID
 * @property int $CaredHasOneSecondID
 * @method DotNotationCaredBelongsTo CaredBelongsToFirst()
 * @method DotNotationCaredBelongsTo CaredBelongsToSecond()
 * @method DotNotationCaredHasOne CaredHasOneFirst()
 * @method DotNotationCaredHasOne CaredHasOneSecond()
 * @method HasManyList|DotNotationCaredHasMany[] CaredHasManyFirst()
 * @method HasManyList|DotNotationCaredHasMany[] CaredHasManySecond()
 * @mixin CacheKeyExtension
 */
class DotNotationCaresPage extends Page implements TestOnly
{
    private static array $has_one = [
        'CaredBelongsToFirst' => DotNotationCaredBelongsTo::class,
        'CaredBelongsToSecond' => DotNotationCaredBelongsTo::class,
        'CaredHasOneFirst' => DotNotationCaredHasOne::class,
        'CaredHasOneSecond' => DotNotationCaredHasOne::class,
    ];

    private static array $has_many = [
        'CaredHasManyFirst' => DotNotationCaredHasMany::class . '.CaresPageFirst',
        'CaredHasManySecond' => DotNotationCaredHasMany::class . '.CaresPageSecond',
    ];

    private static array $cares = [
        'CaredBelongsToFirst',
        'CaredBelongsToSecond',
        'CaredHasOneFirst',
        'CaredHasOneSecond',
        'CaredHasManyFirst',
        'CaredHasManySecond',
    ];

    private static string $table_name = 'DotNotationCaresPage';

    private static bool $has_cache_key = true;
}
