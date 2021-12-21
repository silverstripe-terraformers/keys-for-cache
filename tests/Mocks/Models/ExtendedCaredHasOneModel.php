<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedCaredHasOneModel extends BaseCaredHasOneModel
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedCaredHasOneModel';
}
