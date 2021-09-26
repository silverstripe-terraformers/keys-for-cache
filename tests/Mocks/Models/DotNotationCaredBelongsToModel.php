<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @method DotNotationCaresPage CaresPageFirst()
 * @method DotNotationCaresPage CaresPageSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationCaredBelongsToModel extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $belongs_to = [
        'CaresPageFirst' => DotNotationCaresPage::class . '.CaredBelongsToModelFirst',
        'CaresPageSecond' => DotNotationCaresPage::class . '.CaredBelongsToModelSecond',
    ];

    private static array $cares = [
        'CaresPageFirst',
        'CaresPageSecond',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationCaredBelongsToModel';

    private static bool $has_cache_key = true;
}
