<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Relations;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * @property int $ParentID
 * @property int $CaredThroughID
 * @method CaresPage Parent
 * @method CaredThrough CaredThrough
 */
class CaresPageCaredThrough extends DataObject implements TestOnly
{
    private static string $table_name = 'CaresPageCaredThrough';

    private static array $has_one = [
        'Parent' => CaresPage::class,
        'CaredThrough' => CaredThrough::class,
    ];

    private static array $owned_by = [
        'Parent',
    ];
}
