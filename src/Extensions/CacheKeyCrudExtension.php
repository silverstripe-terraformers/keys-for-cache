<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Versioned\Versioned;
use Terraformers\KeysForCache\State\CacheKeyCrudState;

/**
 * @property ContentController $owner
 */
class CacheKeyCrudExtension extends SiteTreeExtension
{
    public function contentcontrollerInit(ContentController $controller): void
    {
        if ($controller->getRequest()->getVar('CMSPreview')) {
            CacheKeyCrudState::disableRead();
        }

        if ($controller->getRequest()->getVar('stage') !== Versioned::DRAFT) {
            return;
        }

        CacheKeyCrudState::disablePublish();
    }
}
