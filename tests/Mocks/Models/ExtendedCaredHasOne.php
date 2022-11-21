<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

class ExtendedCaredHasOne extends BaseCaredHasOne
{
    private static array $db = [
        'Description' => 'Varchar',
    ];

    private static string $table_name = 'ExtendedCaredHasOne';
}
