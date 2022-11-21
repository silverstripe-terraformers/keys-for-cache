<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

/**
 * This model is referenced by CaresPage as a has_many, meaning that this model has a has_one back to CaresPage
 *
 * @property string $Title
 * @property int $CaresPageFirstID
 * @property int $CaresPageSecondID
 * @method CaresPage CaresPageFirst()
 * @method CaresPage CaresPageSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationCaredHasMany extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'CaresPageFirst' => DotNotationCaresPage::class,
        'CaresPageSecond' => DotNotationCaresPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationCaredHasMany';

    private static bool $has_cache_key = true;
}
