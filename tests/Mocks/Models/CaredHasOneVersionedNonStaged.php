<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @method HasManyList|CaresPage CaresPages()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class CaredHasOneVersionedNonStaged extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_many = [
        'CaresPages' => CaresPage::class,
    ];

    private static array $extensions = [
        Versioned::class.'.versioned',
    ];

    private static string $table_name = 'CaredHasOneVersionedNonStaged';

    private static bool $has_cache_key = false;
}
