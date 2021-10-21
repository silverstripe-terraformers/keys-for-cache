<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Relations;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;

/**
 * @property int $ParentID
 * @property int $CaredThroughModelID
 * @method CaresPage Parent
 * @method CaredThroughModel CaredThroughModel
 */
class CaresPageCaredThroughModel extends DataObject implements TestOnly
{
    private static string $table_name = 'CaresPageCaredThroughModel';

    private static array $has_one = [
        'Parent' => CaresPage::class,
        'CaredThroughModel' => CaredThroughModel::class,
    ];

    private static array $owned_by = [
        'Parent',
    ];
}
