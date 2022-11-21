<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedCaredHasMany extends BaseCaredHasMany
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedCaredHasMany';
}
