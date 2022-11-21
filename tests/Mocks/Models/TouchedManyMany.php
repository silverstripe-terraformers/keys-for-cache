<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

/**
 * @property string $Title
 * @method ManyManyList|TouchesPage TouchesPages()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class TouchedManyMany extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_many_many = [
        'TouchesPages' => TouchesPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'TouchedManyMany';

    private static bool $has_cache_key = true;
}
