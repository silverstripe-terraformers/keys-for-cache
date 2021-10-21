<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Relations;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

/**
 * @property int $ParentID
 * @property int $TouchedThroughModelID
 * @method TouchesPage Parent
 * @method TouchedThroughModel TouchedThroughModel
 */
class TouchesPageTouchedThroughModel extends DataObject implements TestOnly
{
    private static string $table_name = 'TouchesPageTouchedThroughModel';

    private static array $has_one = [
        'Parent' => TouchesPage::class,
        'TouchedThroughModel' => TouchedThroughModel::class,
    ];

    private static array $owned_by = [
        'Parent',
    ];
}
