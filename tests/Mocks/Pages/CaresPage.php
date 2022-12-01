<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsTo;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOne;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneNonVersioned;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneVersionedNonStaged;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaredThrough;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThrough;

/**
 * @property int $CaredBelongsToID
 * @property int $CaredHasOneID
 * @property int $CaredHasOneNonVersionedID
 * @property int $CaredHasOneVersionedNonStagedID
 * @property int $PolymorphicHasOneID
 * @method CaredBelongsTo CaredBelongsTo()
 * @method CaredHasOne CaredHasOne()
 * @method CaredHasOneNonVersioned CaredHasOneNonVersioned()
 * @method CaredHasOneVersionedNonStaged CaredHasOneVersionedNonStaged()
 * @method DataObject PolymorphicHasOne()
 * @method HasManyList|CaredHasMany[] CaredHasMany()
 * @method HasManyList|CaresPageCaredThrough[] CaresPageCaredThrough()
 * @method ManyManyList|CaredManyMany[] CaredManyMany()
 * @method ManyManyThroughList|CaredThrough[] CaredThrough()
 * @method HasManyList|PolymorphicCaredHasMany[] PolymorphicCaredHasMany()
 * @mixin CacheKeyExtension
 */
class CaresPage extends Page implements TestOnly
{
    private static array $has_one = [
        'CaredBelongsTo' => CaredBelongsTo::class,
        'CaredHasOne' => CaredHasOne::class,
        'CaredHasOneNonVersioned' => CaredHasOneNonVersioned::class,
        'CaredHasOneVersionedNonStaged' => CaredHasOneVersionedNonStaged::class,
        'PolymorphicHasOne' => DataObject::class,
    ];

    private static array $has_many = [
        'CaredHasMany' => CaredHasMany::class,
        'CaresPageCaredThrough' => CaresPageCaredThrough::class,
        'PolymorphicCaredHasMany' => PolymorphicCaredHasMany::class . '.PolymorphicHasOne',
    ];

    private static array $many_many = [
        'CaredManyMany' => CaredManyMany::class,
        'CaredThrough' => [
            'through' => CaresPageCaredThrough::class,
            'from' => 'Parent',
            'to' => 'CaredThrough',
        ],
    ];

    private static array $cares = [
        'CaredBelongsTo',
        'CaredHasOne',
        'CaredHasOneNonVersioned',
        'CaredHasOneVersionedNonStaged',
        'CaredHasMany',
        'CaredManyMany',
        'CaredThrough',
        'PolymorphicHasOne',
        'PolymorphicCaredHasMany',
    ];

    private static string $table_name = 'CaresPage';

    private static bool $has_cache_key = true;
}
