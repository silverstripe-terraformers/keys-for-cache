<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchesPage;

/**
 * @property string $Title
 * @method DotNotationTouchesPage TouchesPageFirst()
 * @method DotNotationTouchesPage TouchesPageSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationTouchedBelongsTo extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_to = [
        'TouchesPageFirst' => DotNotationTouchesPage::class . '.TouchedBelongsToFirst',
        'TouchesPageSecond' => DotNotationTouchesPage::class . '.TouchedBelongsToSecond',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationTouchedBelongsTo';

    private static bool $has_cache_key = true;
}
