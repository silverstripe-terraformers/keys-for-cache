<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTreeExtension;
use Terraformers\KeysForCache\State\StagingState;

/**
 * @property ContentController $owner
 */
class StagingExtension extends SiteTreeExtension
{
    public function contentcontrollerInit(ContentController $controller): void
    {
        // If we are currently browsing in a CMSPreview, then we do not want to write or publish any CacheKeys
        if ($controller->getRequest()->getVar('CMSPreview')) { // phpcs:ignore
            StagingState::singleton()->disableWrite();
            StagingState::singleton()->disablePublish();
        }
    }
}
