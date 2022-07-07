<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedPolymorphicCaredHasOneModel extends PolymorphicCaredHasOneModel
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedPolymorphicCaredHasOneModel';
}
