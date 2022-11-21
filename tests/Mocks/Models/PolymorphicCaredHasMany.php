<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @property int $PolymorphicHasOneID
 * @method DataObject PolymorphicHasOne()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class PolymorphicCaredHasMany extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'PolymorphicHasOne' => DataObject::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'PolymorphicCaredHasMany';

    private static bool $has_cache_key = false;
}
