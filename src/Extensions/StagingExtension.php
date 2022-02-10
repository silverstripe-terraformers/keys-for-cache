<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\State\StagingState;

/**
 * @property ContentController $owner
 */
class StagingExtension extends SiteTreeExtension
{
    public function contentcontrollerInit(ContentController $controller): void
    {
        // If we are currently browsing in a CMSPreview, then we do not want to write or publish any CacheKeys
        if ($controller->getRequest()->getVar('CMSPreview')) {
            StagingState::singleton()->disableWrite();
            StagingState::singleton()->disablePublish();

            return;
        }

        // If we are browsing in stage=Stage, then we do not want to publish any CacheKeys
        if ($controller->getRequest()->getVar('stage') === Versioned::DRAFT) { // phpcs:ignore
            StagingState::singleton()->disablePublish();
        }
    }
}
