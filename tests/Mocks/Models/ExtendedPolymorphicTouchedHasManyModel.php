<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedPolymorphicTouchedHasManyModel extends PolymorphicTouchedHasManyModel
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedPolymorphicTouchedHasManyModel';
}