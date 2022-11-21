<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use SilverStripe\ORM\HasManyList;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasMany;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasOne;

/**
 * @property int $BaseCaredHasOneID
 * @method BaseCaredHasOne BaseCaredHasOne()
 * @method HasManyList|BaseCaredHasMany[] BaseCaredHasMany()
 */
class ExtendedCaresPage extends CaresPage
{
    private static array $has_one = [
        'BaseCaredHasOne' => BaseCaredHasOne::class,
    ];

    private static array $has_many = [
        'BaseCaredHasMany' => BaseCaredHasMany::class,
    ];

    private static array $cares = [
        'BaseCaredHasOne',
        'BaseCaredHasMany',
    ];

    private static string $table_name = 'ExtendedCaresPage';
}
