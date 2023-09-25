# Usage and Examples

* [A bit about the `cached` wrapper](#a-bit-about-the-cached-wrapper)
* [Thinking about what you want to cache](#thinking-about-what-you-want-to-cache)
    * [One key to rule them all](#one-key-to-rule-them-all)
    * [Recommended approach](#recommended-approach)
* [The Carousel block example](#the-carousel-block-example)
* [Caching your Blocks/Elements](#caching-your-blockselements)
* [Headers, Footers, and other "global" content areas](#headers-footers-and-other-global-content-areas)
* [Full usage example](#full-usage-example)

## A bit about the `cached` wrapper

The `<% cached ... %>` supports an `if` condition (as of framework 4.7). This can be really useful to use when you have
a `DataObject` or area where you sometimes need it to be uncached.

EG: `<% cached $CacheKey if $MyCondition %>`

You could even use: `<% cached $CacheKey if $CacheKey %>`, and then any time you don't want a particular `DataObject` to
be cached, you could implement the `updateCacheKey(CacheKeyDto $keyDto)` method and set the key to `null`.

## Thinking about what you want to cache

There are multiple sides to the "performance" coin, and we do need to consider these.

### One key to rule them all

**While technically possible, we do not recommend this approach.**

This module is capable of providing you with a single unique key for every `Page` that you **could** wrap around any and
all `Page` content. We can then invalidate that key consistently any time any relevant content changes.

For an end user, this would be very fast **when the cache already exists**. For this user, it would be one lookup for
the `Page` cache key, and then we'd immediately return that cache.

The other side of this coin is what happens when the cache does not exist. Because we only have 1 key for the
entire `Page`, it means that this user will end up needing to regenerate that entire `Page` before they get a response,
and this will be very slow. Also, because we are invaliding our cache often, you are less likely to have users hitting
your cache.

### Recommended approach

We believe there is a common paradigm being used already, where rather than caching the entire `Page`, developers cache
sections of content with separate keys. EG: Each of your Blocks/Elements have their own cache key.

This approach does mean that your end user needs to calculate more keys with each request, however it also means that
you are able to invalidate the cache for smaller portions of content, while persistent many others. The result of this
is that your end users are more likely to hit (at least some of) your caches.

We recommend that you continue to follow a similar approach.

## The Carousel block example

Covered in [Cares](../../README.md#cares) and [Touches](../../README.md#touches) in the main `README.md`, but added here
for completeness.

```php
class CarouselBlock extends BaseElement
{
    /**
     * Adding this configuration will give you access to the getCacheKey() method, and $CacheKey within your template
     * when the block is in scope
     */
    private static bool $has_cache_key = true;

    private static array $has_many = [
        'Items' => CarouselItem::class,
    ];

    /**
     * Tell KFC that your CarouselBlock cares about changes that are made to its Items
     */
    private static array $cares = [
        'Items',
    ];
}
```

```php
class CarouselItem extends DataObject
{
    /**
     * Note that CarouselItem does not have the config $has_cache_key, because we're going to cache at the block level,
     * rather than at the CarouselItem level
     */

    private static array $has_one = [
        'Image' => Image::class,
    ];

    /**
     * Tell KFC that your CarouselItem cares about changes that are made to its Image
     */
    private static array $cares = [
        'Image',
    ];
}
```

The result of this configuration is that the cache key for the `CarouselBlock` will be udpated any time a change is made
to the `CarouselBlock` itself, to one of its `CarouselItems`, or to any `Image` that is assigned to one of the
`CarouselItems`.

See below for how you might then add partial caching using this cache key.

## Caching your Blocks/Elements

Covered above. Let's now discuss how you might use these keys in your template/s now.

One approach would be to implement your own version of `ElementalArea.ss`, and you could determine here that all of your
Blocks/Elements will have a `cached` wrapper.

Save in: `/themes/[name]/templates/DNADesign/Elemental/Models/ElementalArea.ss`:

```silverstripe
<% if $ElementControllers %>
    <% loop $ElementControllers %>
        <% cached $Me.CacheKey if $Me.CacheKey %>
            $Me
        <% end_cached %>
    <% end_loop %>
<% end_if %>
```

Because `$CacheKey` is provided through an extension, if you had some particular Blocks/Elements that you specifically
do **not** want to cache, then you could implement the method yourself in that class, and have it `return null`.

```php
<?php

class MyElement extends BaseElement
{
    public function getCacheKey(): ?string
    {
        // Don't ever cache
        return null;
    }
}
```

Alternatively, you could add your `cached` wrapper in each individual Block/Element template when/if you want to cache
it.

`CarouselBlock.ss`:

```silverstripe
<% cached $CacheKey %>
    <div class="container">
        <% loop $Items %>
            ...
        <% end_loop %>
    </div>
<% end_cached %>
```

And for the Blocks that you **don't** want to cache, you would not add the `<% cached ... %>` wrapper.

## Headers, Footers, and other "global" content areas

We quite often have global footers on our sites - that being, the same footer for every page. For areas like this,
rather than having a `global_cares` for each of your pages, it might make more sense to keep a separate cache key.

You might decide to provide that cache key in the same way that you already do. Using this module doesn't mean you can't
also use your existing mechanisms as well. EG:

```php
class PageController extends ContentController
{
    public function getFooterCacheKey(): string
    {
        return implode(
            '-',
            [
                'Footer',
                SiteTree::get()->count(),
                SiteTree::get()->max('LastEdited'),
            ]
        );
    }
}
```

Or, you could now do (something like) adding a `global_cares` to your `SiteConfig`:

```yaml
SilverStripe\SiteConfig\SiteConfig:
    has_cache_key: true
    global_cares:
        - SilverStripe\CMS\Model\SiteTree
```

Your `SiteConfig` now has a cache key, and that cache key is going to be invalidated any time a change is made to any
`SiteTree` record (essentially the same as your original cache key).

Then your cache key for the footer might be:

```silverstripe
<% cached 'Footer', $SiteConfig.CacheKey %>
    ...
<% end_cached %>
```

Similarly, it's quite common for our Primary Navigation to need to care about global changes to `SiteTree`, but also to
be aware of the "active page", so we might use this same cache key from our `SiteConfig`, and supplement it with the
cache key from the Page itself:

```yaml
Page:
    has_cache_key: true
```

```silverstripe
<% cached 'Navigation', $CacheKey, $SiteConfig.CacheKey %>
    ...
<% end_cached %>
```

**Note:** It is still really performant when we use a mixture of these cache keys together multiple times in our
template, as the values will be in memory after the first time they are used.

## Full usage example

In this example we aim to have cache keys for the following areas:

* Page content: We expect each Block/Element to control its own cache key. We expect the Block/Element cache key to be
  invalidated only when content relevent to it is changed
* Page footer navigation: We expect the footer navigation to be shared globally (**not** unique "per page"), and for it
  to update when changes are made to any `SiteTree` record
* Page primary navigation: We expect the primary navigation to update when changes are made to any `SiteTree` record,
  and we also expect it to be unique per page (so that we can have our "active page" indicators in our nav)

```yaml
# All of our pages should have a cache key
Page:
    has_cache_key: true

# We have added a cache key for our SiteConfig
SilverStripe\SiteConfig\SiteConfig:
    has_cache_key: true
    cares:
        # Our SiteConfig has a couple of CTA buttons available that authors can edit, we want to care about those
        - FacebookLink
        - TwitterLink
    global_cares:
        # SiteTree added as a global care. This will mean that the SiteConfig cache key will be invalidated any time
        # any change is made to a SiteTree record
        - SilverStripe\CMS\Model\SiteTree

# When changes are made to our BlockPage, we want it to "touch" our ElementalArea, this is because some of our Elements
# "care" about changes to the BlockPage (more on this further down)
App\Elemental\BlockPage:
    touches:
        - ElementalArea

# Our Carousel block cares about any changes that are made to its Items. Note, CarouselBlock does *not* care about
# changes to ElementalArea, so its cache key will not be invalidated when changes are made to BlockPage
App\Blocks\CarouselBlock:
    cares:
        - Items

# Our CarouselItem cares about any changes made to its associated Image, or to its CTA button
App\Blocks\CarouselItem:
    cares:
        - Image
        - PrimaryLink

# We have a Block that displays the Page::$Title. Understanding that BlockPage "touches" ElementalArea, this will mean
# that any change to BlockPage willa lso invalidate the cache key for this Block.
App\Blocks\TitleBlock:
    cares:
        - Parent

# If an internal page updates then any associated Link should as well
gorriecoe\Link\Models\Link:
    cares:
        - SiteTree
```

In our `Page.ss` template, we might now have something like this:

```silverstripe

<body>
<%-- Navigation is unique per page, and updates any time a global change is made to any page --%>
<% cached 'Navigation', $CacheKey, $SiteConfig.CacheKey %>
    <% include Navigation %>
<% end_cached %>

<%-- Layout does not have any specific cache key, as this is controlled by each individual Element/Block --%>
$Layout

<%-- Footer is shared globally, and updates any time a global change is made to any page --%>
<% cached 'Footer', $SiteConfig.CacheKey %>
    <% include Footer %>
<% end_cached %>
</body>
```

And (as described earlier), we might implement our own `ElementalArea.ss` to wrap all of my Elements in a `cached` tag:

```silverstripe
<% if $ElementControllers %>
    <% loop $ElementControllers %>
        <% cached $Me.CacheKey %>
            $Me
        <% end_cached %>
    <% end_loop %>
<% end_if %>
```
