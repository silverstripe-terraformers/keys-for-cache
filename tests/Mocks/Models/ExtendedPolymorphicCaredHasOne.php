<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedPolymorphicCaredHasOne extends PolymorphicCaredHasOne
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedPolymorphicCaredHasOne';
}
