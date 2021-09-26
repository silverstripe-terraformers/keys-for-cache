<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Extensions\CacheKeyExtension;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\DotNotationCaresPage;

/**
 * This model is referenced by CaresPage as a has_one, meaning that this model has a has_many back to CaresPage
 *
 * @property string $Title
 * @method HasManyList|CaresPage CaresPagesFirst()
 * @method HasManyList|CaresPage CaresPagesSecond()
 * @mixin Versioned
 * @mixin CacheKeyExtension
 */
class DotNotationCaredHasOneModel extends DataObject implements TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_many = [
        'CaresPagesFirst' => DotNotationCaresPage::class . '.CaredHasOneModelFirst',
        'CaresPagesSecond' => DotNotationCaresPage::class . '.CaredHasOneModelSecond',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'DotNotationCaredHasOneModel';

    private static bool $has_cache_key = true;
}
