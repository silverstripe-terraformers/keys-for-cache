<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchedPage;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @method TouchedPage TouchedPage()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class TouchesBelongsTo extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_to = [
        'TouchedPage' => TouchedPage::class,
    ];

    private static array $touches = [
        'TouchedPage',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'TouchesBelongsTo';

    private static bool $has_cache_key = false;
}
