# Cache Keys

![Build Status](https://github.com/silverstripe-terraformers/keys-for-cache/actions/workflows/main.yml/badge.svg)
[![codecov](https://codecov.io/gh/silverstripe-terraformers/keys-for-cache/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe-terraformers/keys-for-cache)

This module helps you create singular cache keys which can be used as part of any cache strategy, but probably most
commonly with [Silverstripe's Partial Caching](https://docs.silverstripe.org/en/4/developer_guides/performance/partial_caching/)
feature.

**The overall aim of this module is twofold:**

1) Make it easier for developers to create cache keys for our `DataObjects` (especially for those that have complex
   dependencies).
2) Increase the performance of our applications by reducing the number of, and the complexity of the cache keys that we
   calculate at the time of an end user's request.

**How will we achieve these goals?**\
We will use and enhance existing config paradigms that Silverstripe developers are already familiar with.

We will move the cost of calculating cache keys to when the changes are made to our `DataObjects`, rather than at the
time of an end user's request.

**What this module is not**:\
This module is not here to provide a caching method, it is here to provide you with **keys** that you can use with your
preferred caching method (EG: partial caching).

* [Installation](#installation)
* [Why cache keys are difficult](#why-cache-keys-are-difficult)
* [How we aim to solve these difficulties](#how-we-aim-to-solve-these-difficulties)
* [Setup and configuration](#setup-and-configuration)
    * [Has cache key](#has-cache-key)
    * [Cares](#cares)
    * [Touches](#touches)
    * [Global cares](#global-cares)
    * [Usage and Examples](docs/en/examples.md)
* [Performance impact/considerations](#performance-impactconsiderations)
    * [Queued jobs](#queued-jobs)
* [Case Studies](docs/en/case-studies.md)
* [Fluent support](docs/en/fluent.md)
* [Unit testing](docs/en/unit-testing.md)
* [License](license.md)
* [Maintainers](#maintainers)
* [Development and contribution](#development-and-contribution)

## Installation

```
composer require silverstripe-terraformers/keys-for-cache
```

## Requirements

* PHP `^8.1`
* Silverstripe Framework `^5`

Support for Silverstripe 4 is provided though our `^1` tagged releases.

## Why cache keys are difficult

TL;DR: Cache keys need to be updated any time a related piece of information changes, and we often have structures
of content (EG: Carousel blocks) that contain many levels of nested `DataObjects`.

The goal of any cache key is to have as low a cost as possible to calculate (as this must happen with every request),
but also for it to invalidate at the appropriate times (IE: when a piece of relevant content has changed).

Consider the following:

* We have a `Page` with the Elemental module applied.
* One of the blocks available is a Carousel block. This block itself contains Carousel Items, and each Item contains an
  Image.
* When a change is made to the block itself, to one of its Items, or to any of the Images assigned to its Items, the
  cache key should be invalidated.

A cache key for a single Carousel block might look something like:

```php
public function getCacheKey(): string
{
    $parts = [
        // Parts related to the block itself
        static::class,
        $this->ID,
        $this->LastEdited,
        // Parts related to the Items within the block
        $this->Items()->max('LastEdited'),
        $this->Items()->count(),
        // Parts related to the Images assigned to our Items
        Image::get()->filter('ID', $this->Items()->column('ImageID'))->max('LastEdited'),
        Image::get()->filter('ID', $this->Items()->column('ImageID'))->count(),
    ];

    return implode('-', $parts);
}
```

This can be optimised a bit, but the point is: It requires multiple queries each time it is performed, and it requires a
decent chunk of cognitive load to understand (and to come up with in the first place).

In short, we want to achieve the same outcome as the example above, but with a simple configuration, like this:

```yaml
App\Blocks\CarouselBlock:
    has_cache_key: true
    cares:
        - Items

App\Blocks\CarouselItem:
    cares:
        - Image
```

## Setup and configuration

**Preamble:** When we talk about "changes to records", this includes all C.R.U.D. actions.

**Relationship config:** In order for this module to function, we often need to understand more about the relationships
that your `DataObjets` have than perhaps Silverstripe ORM does. [More on this here](docs/en/relationship-config.md).

### Has cache key

First, you need to tell us which DataObjects you would like cache keys to be generated for. For example, we might like a
key for all pages and all Elements:

```yaml
Page:
    has_cache_key: true

DNADesign\Elemental\Models\BaseElement:
    has_cache_key: true
```

By adding this configuration, you will have access to the `getCacheKey()` method on your `DataObject`, and
the `$CacheKey` variable in your template when you have that `DataObject` in scope.

Next, in order to make sure that your cache keys invalidate when related `DataObjects` are updated, you need to define
any dependencies that your model might have.

There are three important configurations to be aware of:

* [Cares](#cares)
* [Touches](#touches)
* [Global cares](#global-cares)

### Cares

This configuration determines how `$this` `DataObject` will be affected when **other** (related) `DataObjects` are
manipulated.

For example: We have requested that all Elements have cache keys. If you have a `CarouselBlock` that contains
`CarouselItems`, then you will want to make sure that the cache key for the `CarouselBlock` is invalidated any time
a `CourselItem` is updated.

You can do that by telling us that your `CarouselBlock` `cares` about its `Items`.

```yaml
App\Blocks\CarouselBlock:
    cares:
        - Items
```

Or in your class:

```php
class CarouselBlock extends BaseElement
{
    private static array $has_many = [
        'Items' => CarouselItem::class,
    ];

    // $owns is optional, but quite common in this use case. I've added it here simply to illustrate how $cares
    // follows the same paradigm
    private static array $owns = [
        'Items',
    ];

    private static array $cares = [
        'Items',
    ];
}
```

Take the original example where we also wanted our `CarouselItem` to include changes to Images as part of its cache key.
We could now also add a `cares` config to our `CarouselItem`:

```yaml
App\Blocks\CarouselItem:
    cares:
        - Image
```

Or in your class like so:

```php
class CarouselItem extends DataObject
{
    private static array $has_one = [
        'Image' => Image::class,
    ];

    // $owns is optional, but quite common in this use case. I've added it here simply to illustrate how $cares
    // follows the same paradigm
    private static array $owns = [
        'Image',
    ];

    private static array $cares = [
        'Image',
    ];
}
```

Now whenever the linked `Image` is updated, it will also update the `CarouselItem`, and in turn the `CarouselItem` will
update the linked `Carousel`. Taking this a step further all the way back to `Page`, we could also add the following:

```yaml
# Our BlockPage cares about changes to its ElementalArea
App\Elemental\BlockPage:
    cares:
        - ElementalArea

# Our ElementalAreas care about any changes made to its Elements
DNADesign\Elemental\Models\ElementalArea:
    cares:
        Elements: BaseElement::class
```

Our `BlockPage` now `cares` about our `ElementalArea`, and our `ElementalArea` now cares about all of its
blocks/elements. This now means that any time a change is made to any of our blocks (so long as we have configured them
similarly to how we have shown with the `Carousel`), we will get a new cache key value on our `Page`.

Alternatively, you could achieve the same outcome by using [Touches](#touches).

**Important note:** Your `DataObject` does not need to `has_cache_key` in order for it to `care` or `touch` other
`DataObjects`. In fact, we very much rely on you providing us with the full relationship tree through
`cares/touches`. We will only generate the cache key for `DataObjects` that `has_cache_key`, but we will continue to
follow the paths you create until we run out of them.

**Important note:** Definitely consider the performance consideration of invalidating your `Page` cache any time an
element is updated. It has been added above purely as an example of what it technically possible; it has not been added
as a recommendation.

### Touches

This configuration determines how `$this` `DataObject` will affect others when it is updated. EG:
when `$this` `DataObject` is updated, it should "touch" some other `DataObjects` so that they too have their cache keys
invalidated.

Using the example from above, if you have a `CarouselBlock` with `CarouselItems` then you could alternatively configure
it like so (where the `key` is the field relationship name, and the value is the `class` that it relates to):

```yaml
App\Blocks\CarouselItem:
    touches:
        - Parent
```

Or in your class like so:

```php
class CarouselItem extends DataObject
{
    private static array $has_one = [
        'Parent' => CarouselBlock::class,
    ];

    private static array $touches = [
        'Parent',
    ];
}
```

This would mean that any time the `CarouselItem` is updated it would also update the cache key of the parent
`CarouselBlock`.

Alternatively, you could achieve the same outcome by using [Cares](#cares).

**Important note:** Your `DataObject` does not need to `has_cache_key` in order for it to `care` or `touch` other
`DataObjects`. In fact, we very much rely on you providing us with the full relationship tree through
`cares/touches`. We will only generate the cache key for `DataObjects` that `has_cache_key`, but we will continue to
follow the paths you create until we run out of them.

### Global cares

You might run into situations where you need your cache key to be updated when *any* DataObject of a certain class is
changed. For example, we could have a "Recent updates block", which lists out the pages that have been most recently
updated. For this, we would want to make it so that when *any* page is updated, it will also invalidate the cache keys
for any DataObject that cares about global changes to our pages.

```yaml
App\Blocks\RecentUpdates:
    global_cares:
        - SilverStripe\CMS\Model\SiteTree
```

**Important note:** These global updates won't use the touches/cares when they occur. For example,
if `Blocks\RecentUpdates` had a `touches` of `Link:  SilverStripe\CMS\Model\SiteTree`, The site tree wouldn't be
updated. This is a mechanism of global updates to ensure we don't run into performance issues

### Usage and examples

See: [Usage and Examples](docs/en/examples.md)

## Performance impact/considerations

This will increase the queries to the database when `DataObjects` are updated, but so far there has been no noticeable
increase to (eg) the publishing time of pages within the CMS, nor has there been any noticeable increase in processing
time of cron jobs related to the manipulation of `DataObjects`.

**That said:**

* You should still be aware of what `cares` and `touches` configuration you enabled.
* If you start to notice performance issues with (eg) Publishing a page, then you might need to reconsider the scope
  of relationships that you `cares` or `touches` as part of your page and related `DataObjects` (EG: blocks).

### Queued jobs

If you want to prevent content authors from getting slightly slower responses when editing in the CMS, you can queue a
job to generate the cache updates by injecting over `CacheKeyExtension` and updating `triggerEvent` to create a job then
call `CacheRelationService::singleton()->processChange($this->DataObject)` in the job.

## Case studies

See: [Case studies](docs/en/case-studies.md)

## Fluent support

See: [Fluent support](docs/en/fluent.md)

## Unit testing

See: [Unit testing](docs/en/unit-testing.md)

## License

See [License](license.md)

## Maintainers

* Adrian Humphreys <adrhumphreys@gmail.com>
* Chris Penny <chris.penny@gmail.com>
* Adrian Jimson <adrian.jimson@silverstripe.com>

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module
maintainers.
