<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

/**
 * This model is referenced by TouchedPage as a has_many, meaning that this model has a has_one back to TouchedPage
 *
 * @property string $Title
 * @property int $TouchesPageID
 * @method TouchesPage TouchesPage()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class TouchedHasManyModel extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'TouchesPage' => TouchesPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'TouchedHasManyModel';

    private static bool $has_cache_key = true;
}
