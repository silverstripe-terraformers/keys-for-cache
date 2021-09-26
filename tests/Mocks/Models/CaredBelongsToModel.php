<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @method CaresPage CaresPage()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class CaredBelongsToModel extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_to = [
        'CaresPage' => CaresPage::class,
    ];

    private static array $cares = [
        'CaresPage',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'CaredBelongsToModel';

    private static bool $has_cache_key = true;
}
