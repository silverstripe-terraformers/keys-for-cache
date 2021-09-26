<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchesBelongsToModel;

/**
 * @property int $TouchesBelongsToModelID
 * @method TouchesBelongsToModel TouchesBelongsToModel()
 * @mixin CacheKeyExtension
 */
class TouchedPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchesBelongsToModel' => TouchesBelongsToModel::class,
    ];

    private static string $table_name = 'TouchedPage';

    private static bool $has_cache_key = true;
}
