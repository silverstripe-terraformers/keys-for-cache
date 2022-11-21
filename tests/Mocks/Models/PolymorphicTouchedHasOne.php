<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

/**
 * This model is referenced by TouchedPage as a has_one, meaning that this model has a has_many back to TouchedPage
 *
 * @property string $Title
 * @method HasManyList|TouchesPage[] TouchesPages()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class PolymorphicTouchedHasOne extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_many = [
        'TouchesPages' => TouchesPage::class . '.PolymorphicTouchedHasOne',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'PolymorphicTouchedHasOne';

    private static bool $has_cache_key = true;
}
