<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedCaredHasManyModel extends BaseCaredHasManyModel
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedCaredHasManyModel';
}
