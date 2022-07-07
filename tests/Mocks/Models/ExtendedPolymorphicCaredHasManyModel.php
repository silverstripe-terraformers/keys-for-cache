<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedPolymorphicCaredHasManyModel extends PolymorphicCaredHasManyModel
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedPolymorphicCaredHasManyModel';
}
