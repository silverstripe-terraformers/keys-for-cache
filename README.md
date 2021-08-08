# Cache Keys

This module helps you create singular cache keys which can be used in your templates as part
of [Silverstripe's Partial Caching](https://docs.silverstripe.org/en/4/developer_guides/performance/partial_caching/)
feature.

The overall aim of this module is twofold:

1) Make it easier for developers to create cache keys for our DataObjects (especially for those that have complex
   dependencies).
2) Increase the performance of our applications by reducing the number of, and the complexity of the cache keys that we
   calculate at the time of an end user's request.

* [API warning](#api-warning)
* [Installation](#installation)
* [Why cache keys are difficult](#why-cache-keys-are-difficult)
* [How we aim to solve these difficulties](#how-we-aim-to-solve-these-difficulties)
* [Setup and configuration](#setup-and-configuration)
    * [Has cache key](#has-cache-key)
    * [Touches](#touches)
    * [Cares](#cares)
    * [Global cares](#global-cares)
    * [Headers, Footers, and other "global" content areas](#headers-footers-and-other-global-content-areas)
    * [Example config and usage](#example-config-and-usage)
* [Performance impact/considerations](#performance-impactconsiderations)
    * [Queued jobs](#queued-jobs)
* [License](#license)
* [Maintainers](#maintainers)
* [Development and contribution](#development-and-contribution)

## API warning:

This module is still in the early days of its life and therefore could have its API changed/updated

## Installation

```
composer require silverstripe-terraformers/keys-for-cache
```

## Why cache keys are difficult

The goal of any cache key is to have as low a cost as possible to calculate (as this must happen with every request),
but also for it to invalidate at the appropriate times (IE: when a piece of relevant content has changed).

Consider the following:

* We have a `Page` Model with the Elemental module applied.
* One of the blocks available is a Carousel block. This block itself contains Carousel Items, and each Item contains an
  Image.
* When a change is made to the block itself, to one of its Items, or to any of the Images assigned to its Items, the
  cache key should be invalidated.

A cache key just for one Carousel block might look something like:

```php
$parts = [
    static::class,
    $this->ID,
    $this->LastEdited,
    $this->Items()->max('LastEdited'),
    $this->Items()->count(),
];
```

This initial cache key is simply enough, but it is missing one critical piece, which is that we are not currently
invalidating the cache key if one of the Images assigned to an Item changes.

* One option would be for us to now loop through each Item and find the `ID` and `LastEdited` date of each assigned
  Image, but doing this is very costly when it comes to our "cost to calculate".
* Another option would be to add `Image::get()->max('LastEdited')` to our cache key. This has a low cost to calculate,
  but it will mean that we invalidate the cache key for each Carousel block any time any Image is changed.

Neither option is great, but it is something that must be solved.

Assuming we find our solution, we still end up needing one cache key per block; if our page contains many blocks, then
we have many cache keys that we need to calculate in order to have an accurate cache key for a single page.

## How we aim to solve these difficulties

In short:
We want to move the cost of calculating cache keys to when the changes are made in the CMS, rather than at the time of
an end user's request. We will do this by having you configure the links between dependencies, and then we'll manage
updating any relevant cache keys when those dependencies change.

## Setup and configuration

### Has cache key

First, you need to tell us which DataObjects you would like cache keys to be generated for. For example, we might like a
key for all pages:

```yaml
Page:
    has_cache_key: true
```

By adding this configuration, you will have access to the `getCacheKey()` method on your DataObject.

Next, you need to define your dependencies (how your DataObjects relate to each other). There are two important
configurations to be aware of:

* [Touches](#touches)
* [Cares](#cares)

### Touches

This configuration determines how `$this` DataObject will affect others when it is updated. IE: when `$this` DataObject
is updated, it should "touch" some other DataObjects.

For example if you have a `Carousel` with `CarouselItems` then you might configure it like so:

```yaml
App\Blocks\CarouselItem:
    touches:
        Parent: App\Blocks\CarouselBlock
```

Or in your class like so:

```php
class CarouselBlockItem extends DataObject
{
    private static array $has_one = [
        'Parent' => Carousel::class,
    ];

    private static array $touches = [
        'Parent' => Carousel::class,
    ];
}
```

Where the `key` is the field relationship name, and the value is the `class` that it relates to:

This would mean that any time the Carousel Item is updated it would also update the parent Carousel (and if that parent
had a cache key then it would also update its cachekey)

Alternatively, you could achieve the same outcome by using [Cares](#cares).

### Cares

Take the above example where you have a `Carousel` with `CarouselItems`, rather than using [Touches](#touches), you
could instead use `cares`, like so:

```yaml
App\Blocks\CarouselBlock:
    cares:
        Items: App\Blocks\CarouselItem
```

Or in your class like so:

```php
class CarouselBlock extends BaseElement
{
    private static array $has_many = [
        'Items' => CarouselItem::class,
    ];

    private static array $cares = [
        'Items' => CarouselItem::class,
    ];
}
```

Take the original example where we also wanted to include changes to Images as part of our cache key. We could now also
add a `cares` config to our `CarouselItem`:

```yaml
App\Blocks\CarouselItem:
    cares:
        Image: SilverStripe\Assets\Image
```

Or in your class like so:

```php
class CarouselBlockItem extends DataObject
{
    private static array $has_one = [
        'Image' => Image::class,
    ];

    private static array $cares = [
        'Image' => Image::class,
    ];
}
```

Now whenever the linked image is updated, it will also update the carousel item. The carousel item will then also update
the linked carousel. Taking this a step further all the way back to `Page`, we can also add the following:

```yaml
Page:
    cares:
        ElementalArea: DNADesign\Elemental\Models\ElementalArea

DNADesign\Elemental\Models\ElementalArea:
    cares:
        Elements: BaseElement::class
```

Our `Page` now `cares` about our `ElementalArea`, and our `ElementalArea` now cares about all of its blocks/elements.
This now means that any time a change is made to any of our blocks (so long as we have configured them similarly to how
we have shown with the `Carousel`), we will get a new cache key value on our `Page`.

### Global cares

You might run into situations where you need your cache key to be updated when *any* DataObject of a certain class is
changed. For example, we could have a "Recent updates block", which lists out the pages that have been most recently
updated. For this, we would want to make it so that when *any* page is updated, it will also invalidate the cache keys
for any DataObject that cares about global changes to our pages.

```yaml
App\Blocks\RecentUpdates:
    global_cares:
        SiteTree: SilverStripe\CMS\Model\SiteTree
```

**Important note:** These global updates won't use the touches/cares when they occur. For example,
if `Blocks\RecentUpdates` had a `touches` of `Link:  SilverStripe\CMS\Model\SiteTree`, The site tree wouldn't be
updated. This is a mechanism of global updates to ensure we don't run into performance issues

### Headers, Footers, and other "global" content areas

We quite often have global footers on our sites - that being, the same footer for every page. For areas like this,
rather than having a `global_cares` for each of your pages, it might make more sense to keep a separate cache key.

You might decide to just provide that cache key in (probably) the same way that you already do. EG:

```php
class PageController extends ContentController
{
    public function getFooterCacheKey(): string
    {
        return implode(
            '-',
            'Footer',
            SiteTree::get()->count(),
            SiteTree::get()->max('LastEdited')
        );
    }
}
```

Or, you could add a `global_cares` to your `SiteConfig`:

```yaml
SilverStripe\SiteConfig\SiteConfig:
    has_cache_key: true
    cares:
        SiteTree: SilverStripe\CMS\Model\SiteTree
```

And then your cache key for the footer might be:

```silverstripe
<% cached 'Footer', $SiteConfig.CacheKey %>
    ...
<% end_cached %>
```

Similarly, it's quite common for our Primary Navigation to need to care about global changes to `SiteTree`, but also to
be aware of the "active page", so we might use this same cache key from our `SiteConfig`, and supplement it with the
cache key from the Page itself:

```silverstripe
<% cached 'Navigation', $CacheKey, $SiteConfig.CacheKey %>
    ...
<% end_cached %>
```

**Note:* It is still really performant when we use a mixture of these cache keys together, as the values will be in
memory after the first time they are used.

### Example config and usage

In this example we aim to have cache keys for the following areas:

* Page content: We expect the Page content to be unique for each page, and for it to update any time the page itself, or
  any of its related DataObjects are updated
* Page footer navigation: We expect the footer navigation to be shared globally, and for it to update when changes are
  made to any page record
* Page primary navigation: We expect the primary navigation to update when changes are made to any page record, and we
  also expect it to be unique per page (so that we can our "active page" features)

```yaml
# All of our pages should have a cache key
Page:
    has_cache_key: true

# We have also added a cache key for our Site settings, as we have some models that are managed there
SilverStripe\SiteConfig\SiteConfig:
    has_cache_key: true
    cares:
        # Our SiteConfig has a couple of CTA buttons available that authors can edit
        PrimaryButton: gorriecoe\Link\Models\Link
        SecondaryButton: gorriecoe\Link\Models\Link
    global_cares:
        # SiteTree added as a global care, so that we can use this cache key for our global footer that we want to
        # invalidate any time any page updates
        SiteTree: SilverStripe\CMS\Model\SiteTree

# Our BlockPage cares about changes that happen to our ElementalArea
App\Elemental\BlockPage:
    cares:
        ElementalArea: DNADesign\Elemental\Models\ElementalArea

# Our ElementalArea cares about any changes that happen to its Elements
DNADesign\Elemental\Models\ElementalArea:
    cares:
        Elements: DNADesign\Elemental\Models\BaseElement

# Our Carousel block cares about any changes that are made to its Items
App\Blocks\CarouselBlock:
    cares:
        Items: App\Blocks\CarouselItem

# Our CarouselItem cares about any changes made to its associated Image, or to its CTA button
App\Blocks\CarouselItem:
    cares:
        Image: SilverStripe\Assets\Image
        PrimaryLink: gorriecoe\Link\Models\Link

# Our Call to action block has an Image that it cares about, as well as two CTA buttons
App\Blocks\CtaBlock:
    cares:
        Image: SilverStripe\Assets\Image
        PrimaryLink: gorriecoe\Link\Models\Link
        SecondaryLink: gorriecoe\Link\Models\Link

# If an internal page updates then any associated Link should as well
gorriecoe\Link\Models\Link:
    cares:
        SiteTree: SilverStripe\CMS\Model\SiteTree
```

In our template, we might now have something like this:

```silverstripe
<body>
    <%-- Navigation is unique per page, and updates any time a global change is made to any page --%>
    <% cached 'Navigation', $CacheKey, $SiteConfig.CacheKey %>
        <% include Navigation %>
    <% end_cached %>

    <%-- Page content only changes when updates are made to the page or its direct dependencies --%>
    <% cached 'Content', $CacheKey %>
        $Layout
    <% end_cached %>

    <%-- Footer is shared globally, and updates any time a global change is made to any page --%>
    <% cached 'Footer', $SiteConfig.CacheKey %>
        <% include Footer %>
    <% end_cached %>
</body>
```

## Performance impact/considerations

This will increase the queries to the database when DataObjects are updated. We are still pretty early into our
performance tests, but so far it has not created an unreasonable amount of additional load time to author actions.

### Queued jobs

If you want to prevent content authors from getting slightly slower responses when editing in the CMS, you can queue a
job to generate the cache updates by injecting over `CacheKeyExtension` and updating `triggerEvent` to create a job then
call `CacheRelationService::singleton()->processChange($this->DataObject)` in the job.

## License

See [License](license.md)

## Maintainers

* Adrian Humphreys <adrhumphreys@gmail.com>
* Chris Penny <chris.penny@gmail.com>

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module
maintainers.
