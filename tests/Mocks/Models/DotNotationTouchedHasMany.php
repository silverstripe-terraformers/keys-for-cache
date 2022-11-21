<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchesPage;

/**
 * This model is referenced by TouchedPage as a has_many, meaning that this model has a has_one back to TouchedPage
 *
 * @property string $Title
 * @property int $TouchesPageFirstID
 * @property int $TouchesPageSecondID
 * @method DotNotationTouchesPage TouchesPageFirst()
 * @method DotNotationTouchesPage TouchesPageSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationTouchedHasMany extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'TouchesPageFirst' => DotNotationTouchesPage::class,
        'TouchesPageSecond' => DotNotationTouchesPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationTouchedHasMany';

    private static bool $has_cache_key = true;
}
