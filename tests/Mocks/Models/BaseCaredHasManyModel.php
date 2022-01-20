<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;

class BaseCaredHasManyModel extends DataObject
{
    private static array $db = [
        'Title' => 'Varchar',
    ];

    private static array $has_one = [
        'ExtendedCaresPage' => ExtendedCaresPage::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'BaseCaredHasManyModel';

    private static bool $has_cache_key = false;
}
