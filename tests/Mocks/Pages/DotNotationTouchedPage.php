<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Page;
use SilverStripe\Dev\TestOnly;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Models\DotNotationTouchesBelongsTo;

/**
 * @property int $TouchesBelongsToFirstID
 * @property int $TouchesBelongsToSecondID
 * @method DotNotationTouchesBelongsTo TouchesBelongsToFirst()
 * @method DotNotationTouchesBelongsTo TouchesBelongsToSecond()
 * @mixin CacheKeyExtension
 */
class DotNotationTouchedPage extends Page implements TestOnly
{
    private static array $has_one = [
        'TouchesBelongsToFirst' => DotNotationTouchesBelongsTo::class,
        'TouchesBelongsToSecond' => DotNotationTouchesBelongsTo::class,
    ];

    private static string $table_name = 'DotNotationTouchedPage';

    private static bool $has_cache_key = true;
}
