<?php

namespace Terraformers\KeysForCache\Extensions;

use SilverStripe\ORM\DataExtension;
use Terraformers\KeysForCache\DataTransferObjects\CacheKeyDto;
use TractorCow\Fluent\State\FluentState;

class FluentExtension extends DataExtension
{
    public function updateCacheKey(CacheKeyDto $cacheKey): void
    {
        // You aren't using Fluent, so we can't append any Locale to your cache key
        if (!class_exists(FluentState::class)) {
            return;
        }

        $currentLocale = FluentState::singleton()->getLocale();

        // There is no current Locale for us to append
        if (!$currentLocale) {
            return;
        }

        $cacheKey->appendKey($currentLocale);
    }
}
