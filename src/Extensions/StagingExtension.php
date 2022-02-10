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
        if ($controller->getRequest()->getVar('CMSPreview')) {
            StagingState::disableRead();
            StagingState::disablePublish();

            return;
        }

        if ($controller->getRequest()->getVar('stage') !== Versioned::DRAFT) {
            return;
        }

        StagingState::disablePublish();
    }
}
