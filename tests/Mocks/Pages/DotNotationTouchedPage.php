<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchesBelongsToModel;

/**
 * @property int $TouchesBelongsToModelFirstID
 * @property int $TouchesBelongsToModelSecondID
 * @method DotNotationTouchesBelongsToModel TouchesBelongsToModelFirst()
 * @method DotNotationTouchesBelongsToModel TouchesBelongsToModelSecond()
 * @mixin CacheKeyExtension
 */
class DotNotationTouchedPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchesBelongsToModelFirst' => DotNotationTouchesBelongsToModel::class,
        'TouchesBelongsToModelSecond' => DotNotationTouchesBelongsToModel::class,
    ];

    private static string $table_name = 'DotNotationTouchedPage';

    private static bool $has_cache_key = true;
}
