<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataQueryManipulator;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\ORM\ManyManyThroughQueryManipulator;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;

/**
 * You do not need this Extension if you are using `symbiote/silverstripe-gridfieldextensions` version `3.5.0` or newer.
 * Full support for Versioned and non-Versioned DataObjects is out of the box.
 *
 * You also do not need this Extension for Versioned DataObject. They are supported out of the box because
 * GridFieldOrderableRows already performs write() through the ORM when sorting Versioned DataObjects.
 *
 * WARNING: We absolutely plan to remove this extension once GridFieldOrderableRows supports sorting on non-Versioned
 * DataObjects. If you need it, probably best to copy/paste this to your project, and we empower you to own it from that
 * point forward.
 *
 * This Extension is *not* automatically applied because I think you should seriously consider Versioning your
 * DataObject. If you are adding this DataObject to (something like) an Element, which *is* Versioned, then (imo) it is
 * best that all the related DataObjects (like its "Items") are also Versioned. This gives a consistent author
 * experience - where they can have draft/live versions of things. You can then also rely on the existing support from
 * GridFieldOrderableRows.
 *
 * There is a closed ticket on the GridFieldExtensions module that explains the issue (now fixed in v3.5.0):
 * https://github.com/symbiote/silverstripe-gridfieldextensions/issues/335
 *
 * For folks on a version lower than 3.5.0, this Extension provides you a way to support the clearing of CacheKeys on
 * non-Versioned DataObjects when you are using the GridFieldOrderableRows component.
 *
 * This Extension also doesn't have any test coverage (because of everything we mentioned above). It has only gone
 * through manual testing. Use at your own risk and be prepared to submit tickets if you find any issues or use cases
 * that aren't supported.
 */
class GridFieldOrderableRowsExtension extends Extension
{
    /**
     * @param SS_List $list The List of records being sorted
     * @param array $values [listItemID => currentSortValue]
     * @param array $sortedIDs [newSortValue => listItemID]
     * @return void
     */
    public function onAfterReorderItems(SS_List $list, array $values, array $sortedIDs): void
    {
        // We only support this action for DataList and ArrayList (as we know they can hold DataObjects)
        if (!$list instanceof DataList && !$list instanceof ArrayList) {
            return;
        }

        $class = $list->dataClass();
        $isVersioned = false;

        // This is important for two reasons. The first is that we need to know whether we are sorting a Through
        // class or the Relation class. The second is that we need to know if that DataObject is Versioned, if it is
        // then the default GridFieldOrderableRows::reorderItems() will have triggered all the actions we need already
        if ($list instanceof ManyManyThroughList) {
            // We'll be updating the Through class, not the Relation class
            $class = $this->getManyManyInspector($list)->getJoinClass();
            $isVersioned = $class::create()->hasExtension(Versioned::class);
        } elseif (!$this->isManyMany($list)) {
            $isVersioned = $class::create()->hasExtension(Versioned::class);
        }

        // Check to see whether this List would already have been processed through the ORM, and therefor has already
        // triggered the events that we need
        if ($isVersioned) {
            return;
        }

        // We can't do anything with the Ordered DataObject if it doesn't have our CacheKeyExtension applied
        if (!DataObject::has_extension($class, CacheKeyExtension::class)) {
            return;
        }

        // The problem is that $sortedIDs is a list of the _related_ item IDs, which causes trouble
        // with ManyManyThrough, where we need the ID of the _join_ item in order to set the value.
        $itemToSortReference = $list instanceof ManyManyThroughList
            ? 'getJoin'
            : 'Me';
        $currentSortList = $list->map('ID', $itemToSortReference)->toArray();

        // Our List has already been processed and saved at this point, so we cannot access anything like the changed()
        // methods for our DataObjects
        // We do, however, have the original and new sort values, so we can run a comparison on those
        foreach ($sortedIDs as $sortValue => $listItemID) {
            // It should exist, but if it doesn't we'll just ignore it
            if (!array_key_exists($listItemID, $values)) {
                continue;
            }

            // Check to see if the value is still the same as it was before. If it is, then we don't need to do anything
            if ($values[$listItemID] === $sortValue) {
                continue;
            }

            /** @var DataObject|CacheKeyExtension $record */
            $record = $currentSortList[$listItemID];

            // Sanity checks
            if (!$record->isInDB()) {
                continue;
            }

            // We know that we need to publish these events, as this is a non-Versioned DataObject
            $record->triggerCacheEvent(true);
        }
    }

    /**
     * This is a copy/paste of the method used in GridFieldOrderableRow. We need it here as we're performing the same
     * check/s
     *
     * @param SS_List $list
     * @return DataQueryManipulator|ManyManyThroughQueryManipulator|SS_List
     */
    private function getManyManyInspector(SS_List $list)
    {
        $inspector = $list;

        if (!$list instanceof ManyManyThroughList) {
            return $inspector;
        }

        foreach ($list->dataQuery()->getDataQueryManipulators() as $manipulator) {
            if (!$manipulator instanceof ManyManyThroughQueryManipulator) {
                continue;
            }

            return $manipulator;
        }

        return $inspector;
    }

    private function isManyMany(SS_List $list): bool
    {
        return $list instanceof ManyManyList || $list instanceof ManyManyThroughList;
    }
}
