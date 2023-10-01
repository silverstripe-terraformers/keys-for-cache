# Unit Testing

- KFC includes a feature to make sure that it only updates the cache key for any unique record once per request. It
  does this by keeping a local cache of what has been processed already.
- One unit test case is considered to be "one request", which means that within a test case, KFC will only ever update
  the cache key for a record once - even if you perform multiple changes and writes to that record.
- A developer might want to create tests that check their cache key updates when they trigger a change on that record.

Below is an example of a reasonable test that a developer might want to undertake. You would expect that the
`LastEdited` and `getCacheKey()` values update with each `var_dump()`.

```php
class SiteConfigTest extends SapphireTest
{

    protected $usesDatabase = true;

    public function testSiteConfigCacheKey(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        // Output intitial values
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());

        // Sleep for a second to make sure our date changes
        sleep(1);

        // Make a change to SiteConfig
        $siteConfig->Title = 'Updated Site Title 1';
        $siteConfig->write();

        // We would expect these values to change now
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());

        // Sleep for a second to make sure our date changes
        sleep(1);

        // Make a change to SiteConfig
        $siteConfig->Title = 'Updated Site Title 2';
        $siteConfig->write();

        // We would expect these values to change again now
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());
    }

}
```

**Actual:** `LastEdited` does update, but `getCacheKey()` does not.

In order to get the expected outcome, developers need to add `ProcessedUpdatesService::singleton()->flush();` before
they `write()` their record. This clears the local cache of processed records from KFC, and will mean that any new
changes to your records will trigger their cache keys to be updated (even if they have done so previously in this
request).

EG:

```php
class SiteConfigTest extends SapphireTest
{

    protected $usesDatabase = true;

    public function testSiteConfigCacheKey(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        // Output intitial values
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());

        // Sleep for a second to make sure our date changes
        sleep(1);

        // Flush updates from KFC so that new writes invalidate cache keys
        ProcessedUpdatesService::singleton()->flush();

        // Make a change to SiteConfig
        $siteConfig->Title = 'Updated Site Title 1';
        $siteConfig->write();

        // We would expect these values to change now
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());

        // Sleep for a second to make sure our date changes
        sleep(1);

        // Flush updates from KFC so that new writes invalidate cache keys
        ProcessedUpdatesService::singleton()->flush();

        // Make a change to SiteConfig
        $siteConfig->Title = 'Updated Site Title 2';
        $siteConfig->write();

        // We would expect these values to change again now
        var_dump($siteConfig->LastEdited);
        var_dump($siteConfig->getCacheKey());
    }

}
```
