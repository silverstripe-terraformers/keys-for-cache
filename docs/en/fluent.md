# Fluent support

* [Localising your Partial Cache](#localising-your-partial-cache)
    * [Use configuration (ideal)](#use-configuration-ideal)
    * [Use extension (less ideal, but still fine)](#use-extension-less-ideal-but-still-fine)
    * [Add it to your Partial Cache manually](#add-it-to-your-partial-cache-manually)

We have considered Fluent for this module, however, the level of configuration available in Fluent makes it difficult
for us to prescribe a solution at this time.

Fluent itself provides us with the mechanism we require to have a separate cache for each frontend Locale that our users
browse, however, it is important to note that with our "out of the box" implementation, when any Localisation for
a `DataObject` is updated, it will invalidate the cache for **all** Localisations of that particular `DataObject`.

This does mean that you will be invalidating the cache for Locales that might not have been updated, but we feel that
this is acceptable in these early stages of this module's life. The important thing is that the caches **will be
unique** for each Locale that your users browse, and you **will not** have data "leak" from one Locale to another.

## Localising your Partial Cache

This is not specific to Keys for Cache, and is generally recommended for any project using Fluent.

When you are creating your `<% cached %>` wrappers, you will want to make sure that your `CurrentLocale` is appended to
the Key. This is to make sure that you don't ever serve the cache from another Locale accidentally (in the same way that
Silvesrtripe automatically appends your current `Stage` to the Key so that you never serve draft content in live).

There are a couple of ways that we can achieve this.

### Use configuration (ideal)

```yaml
SilverStripe\View\SSViewer:
    global_key: '$CurrentReadingMode, $CurrentUser.ID, $CurrentLocale'
```

`SSViewer` already has configuration available called `global_key`. The default value is:
`$CurrentReadingMode, $CurrentUser.ID`

Fluent provides us with a template variable `$CurrentLocale`, so we can add this variable to the `global_key`.

### Use extension (less ideal, but still fine)

```yaml
SilverStripe\ORM\DataObject:
    extensions:
        FluentExtensions: Terraformers\KeysForCache\Extensions\FluentExtension
```

We have provided a very basic `FluentExtension` which you can apply. This extension implements the `updateCacheKey()`
method so that any usages of `getCacheKey()` or `$CacheKey` will have your current Locale appended to it.

You could decide to only apply this to certain classes, if you would prefer.

This is less ideal than using the config purely because it's slightly slower.

### Add it to your Partial Cache manually

```silverstripe
<% cached $CacheKey, $CurrentLocale %>
    ...
<% end_cached %>
```

You can also implement `$CurrentLocale` in your `cached` wrappers yourself when you need it.
