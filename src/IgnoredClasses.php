<?php

namespace Terraformers\KeysForCache;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Model\VirtualPage;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionRole;
use SilverStripe\ShareDraftContent\Models\ShareToken;
use SilverStripe\Versioned\ChangeSet;
use SilverStripe\Versioned\ChangeSetItem;
use TractorCow\Fluent\Model\Domain;
use TractorCow\Fluent\Model\FallbackLocale;
use TractorCow\Fluent\Model\Locale;

class IgnoredClasses
{
    use Injectable;

    public function get(): array
    {
        return [
            ChangeSet::class,
            ChangeSetItem::class,
            CacheKey::class,

            SiteTree::class,

            // Questionable filters...
//            Member::class,
//            Group::class,
//            ShareToken::class,
//            VirtualPage::class,
//            Locale::class,
//            Domain::class,
//            FallbackLocale::class,
//            Permission::class,
//            PermissionRole::class,
        ];
    }

    public function filter(array $classes): array
    {
        $ignoredClasses = IgnoredClasses::singleton()->get();

        // Remove ignored classes
        return array_filter($classes, function (string $className) use ($ignoredClasses): bool {
            return !in_array($className, $ignoredClasses);
        });
    }
}
