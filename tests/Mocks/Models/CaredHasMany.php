<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * This model is referenced by CaresPage as a has_many, meaning that this model has a has_one back to CaresPage
 *
 * @property string $Title
 * @property int $CaresPageID
 * @method CaresPage CaresPage()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class CaredHasMany extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'CaresPage' => CaresPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'CaredHasMany';

    private static bool $has_cache_key = false;
}
