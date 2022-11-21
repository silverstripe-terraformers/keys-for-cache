<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedPolymorphicCaredHasMany extends PolymorphicCaredHasMany
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedPolymorphicCaredHasMany';
}
