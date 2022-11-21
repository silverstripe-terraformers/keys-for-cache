<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Relations\TouchesPageTouchedThrough;

/**
 * @property int $TouchedBelongsToID
 * @property int $TouchedHasOneID
 * @property int $PolymorphicHasOneID
 * @method DataObject PolymorphicHasOne()
 * @method HasManyList|PolymorphicTouchedHasMany[] PolymorphicTouchedHasMany()
 * @method TouchedBelongsTo TouchedBelongsTo()
 * @method TouchedHasOne TouchedHasOne()
 * @method HasManyList|TouchedHasMany[] TouchedHasMany()
 * @method ManyManyList|TouchedManyMany[] TouchedManyMany()
 * @method ManyManyThroughList|TouchedManyMany[] TouchedThrough()
 * @mixin CacheKeyExtension
 */
class TouchesPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchedBelongsTo' => TouchedBelongsTo::class,
        'TouchedHasOne' => TouchedHasOne::class,
        'PolymorphicHasOne' => DataObject::class,
    ];

    private static array $has_many = [
        'TouchedHasMany' => TouchedHasMany::class,
        'PolymorphicTouchedHasMany' => PolymorphicTouchedHasMany::class . '.PolymorphicHasOne',
    ];

    private static array $many_many = [
        'TouchedManyMany' => TouchedManyMany::class,
        'TouchedThrough' => [
            'through' => TouchesPageTouchedThrough::class,
            'from' => 'Parent',
            'to' => 'TouchedThrough',
        ],
    ];

    private static array $touches = [
        'PolymorphicHasOne',
        'PolymorphicTouchedHasMany',
        'TouchedBelongsTo',
        'TouchedHasOne',
        'TouchedHasMany',
        'TouchedManyMany',
        'TouchedThrough',
    ];

    private static string $table_name = 'TouchesPage';

    private static bool $has_cache_key = false;
}
