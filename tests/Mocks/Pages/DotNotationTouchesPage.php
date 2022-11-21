<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchedHasOne;

/**
 * @property int $TouchedBelongsToFirstID
 * @property int $TouchedBelongsToSecondID
 * @property int $TouchedHasOneFirstID
 * @property int $TouchedHasOneSecondID
 * @method DotNotationTouchedBelongsTo TouchedBelongsToFirst()
 * @method DotNotationTouchedBelongsTo TouchedBelongsToSecond()
 * @method DotNotationTouchedHasOne TouchedHasOneFirst()
 * @method DotNotationTouchedHasOne TouchedHasOneSecond()
 * @method HasManyList|DotNotationTouchedHasMany[] TouchedHasManyFirst()
 * @method HasManyList|DotNotationTouchedHasMany[] TouchedHasManySecond()
 * @mixin CacheKeyExtension
 */
class DotNotationTouchesPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchedBelongsToFirst' => DotNotationTouchedBelongsTo::class,
        'TouchedBelongsToSecond' => DotNotationTouchedBelongsTo::class,
        'TouchedHasOneFirst' => DotNotationTouchedHasOne::class,
        'TouchedHasOneSecond' => DotNotationTouchedHasOne::class,
    ];

    private static array $has_many = [
        'TouchedHasManyFirst' => DotNotationTouchedHasMany::class . '.TouchesPageFirst',
        'TouchedHasManySecond' => DotNotationTouchedHasMany::class . '.TouchesPageSecond',
    ];

    private static array $touches = [
        'TouchedBelongsToFirst',
        'TouchedBelongsToSecond',
        'TouchedHasOneFirst',
        'TouchedHasOneSecond',
        'TouchedHasManyFirst',
        'TouchedHasManySecond',
    ];

    private static string $table_name = 'DotNotationTouchesPage';

    private static bool $has_cache_key = false;
}
