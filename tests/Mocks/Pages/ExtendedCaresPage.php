<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Pages;

use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\BaseCaredHasOneModel;

class ExtendedCaresPage extends CaresPage
{
    private static array $has_one = [
        'BaseCaredHasOneModel' => BaseCaredHasOneModel::class,
    ];

    private static array $has_many = [
        'BaseCaredHasManyModels' => BaseCaredHasManyModel::class,
    ];

    private static array $cares = [
        'BaseCaredHasOneModel',
        'BaseCaredHasManyModels',
        'ExtendedCaredHasOneModel',
        'ExtendedCaredHasManyModels',
    ];

    private static string $table_name = 'ExtendedCaresPage';
}
