<?php

namespace Terraformers\KeysForCache\Tests\Mocks\Relations;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

/**
 * @property int $ParentID
 * @property int $TouchedThroughID
 * @method TouchesPage Parent
 * @method TouchedThrough TouchedThrough
 */
class TouchesPageTouchedThrough extends DataObject implements TestOnly
{
    private static string $table_name = 'TouchesPageTouchedThrough';

    private static array $has_one = [
        'Parent' => TouchesPage::class,
        'TouchedThrough' => TouchedThrough::class,
    ];

    private static array $owned_by = [
        'Parent',
    ];
}
