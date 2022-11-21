<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationTouchedPage;

/**
 * @property string $Title
 * @method DotNotationTouchedPage TouchedPageFirst()
 * @method DotNotationTouchedPage TouchedPageSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationTouchesBelongsTo extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_to = [
        'TouchedPageFirst' => DotNotationTouchedPage::class . '.TouchesBelongsToFirst',
        'TouchedPageSecond' => DotNotationTouchedPage::class . '.TouchesBelongsToSecond',
    ];

    private static array $touches = [
        'TouchedPageFirst',
        'TouchedPageSecond',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationTouchesBelongsTo';

    private static bool $has_cache_key = false;
}
