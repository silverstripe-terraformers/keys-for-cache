# Cache Keys
This module helps you create singular cache keys which can be used in your templates. The idea is that you will configure the links between dependencies and then we'll manage updating the keys when those dependencies change

## API warning:
This module is still in the early days of its life and therefore could have its API changed/updated

## Installation
```
composer require silverstripe-terraformers/keys-for-cache
```

## Config
You'll need to specify where you're using the cache key, for example we usually use a key on all pages:
```yaml
Page:
  has_cache_key: true
```

You will then need to configure you're `touches`, that is, when this thing is updated it should "touch" the other thing. For example if you had a `Carousel` with `CarouselItems` then you might configure it like so:
```yaml
App\Carousel\CarouselItem:
  touches:
    Parent: App\Carousel\Carousel
```

This would mean that any time the carousel item is updated it would also update the parent carousel (and if that parent had a cache key then it would also update it's cachekey)

We could then imagine one of our items having an `Image` we would configure it with the `cares` config:
```yaml
App\Carousel\CarouselItem:
  cares:
    Image: SilverStripe\Assets\Image
```

Now whenever the linked image is updated, it will also update the carousel item. The carousel item will then update the linked carousel.

### Global cares
You might run into places where the thing you care about is dynamic but only updates through content author action. For example we could have a latest updates block that lists out pages that have been recently updated. For this we would want to make it so any time a page was updated it would also cause all the cache keys to update for the blocks that exist (since every block would now be come invalid)
```yaml
App\RecentUpdates\Block:
  global_cares:
    SiteTree: SilverStripe\CMS\Model\SiteTree
```

It's good to mention here that these global updates won't use the touches/cares when they occur so for example if `RecentUpdates\Block` had a `touches` of `Link:  SilverStripe\CMS\Model\SiteTree`, The site tree wouldn't be updated. This is a mechanism of global updates to ensure we don't run into performance issues

### Example config with Elemental
```yaml
# All of our pages should have a cache key
Page:
  has_cache_key: true

SilverStripe\SiteConfig\SiteConfig:
  has_cache_key: true
  cares:
    PrimaryButton: gorriecoe\Link\Models\Link
    SecondaryButton: gorriecoe\Link\Models\Link
    HeaderLinks: gorriecoe\Link\Models\Link
    SearchPage: SilverStripe\CMS\Model\SiteTree

# If the block page is updated then it should update the elemental area
App\Elemental\BlockPage:
  cares:
    ElementalArea: DNADesign\Elemental\Models\ElementalArea
  touches:
    ElementalArea: DNADesign\Elemental\Models\ElementalArea

# If an elemental area is updated, then it should update the base elements
DNADesign\Elemental\Models\ElementalArea:
    touches:
        Elements: DNADesign\Elemental\Models\BaseElement

# If an element is updated, then it should update the area
DNADesign\Elemental\Models\BaseElement:
    has_cache_key: true
    touches:
        Parent: DNADesign\Elemental\Models\ElementalArea

# If an internal page updates then the link should too
gorriecoe\Link\Models\Link:
    cares:
        SiteTree: SilverStripe\CMS\Model\SiteTree

App\DecisionTree\DecisionTreeBlock:
    cares:
        Answers: App\DecisionTree\DecisionTreeAnswer
App\DecisionTree\DecisionTreeQuestion:
    cares:
        Answers: App\DecisionTree\DecisionTreeAnswer.Parent
        OtherAnswers: App\DecisionTree\DecisionTreeAnswer.OtherParentQuestion

App\Elemental\Blocks\HeroBlock:
    cares:
        PrimaryLink: gorriecoe\Link\Models\Link
        SecondaryLink: gorriecoe\Link\Models\Link

# HeroImageBlock extends HeroBlock, therefore we just need to add Image
App\Elemental\Blocks\HeroImageBlock:
    cares:
        Image: SilverStripe\Assets\Image
```

## Performance impact/considerations
This will increase the queries to the database when records are added (if they have applicable config (e.. Member might not have any performance impacts))

### Queued jobs
If you want to prevent content authors from getting slightly slower responses when editing in the CMS you can queue a job to generate the cache updates by injecting over `CacheKeyExtension` and updating `triggerEvent` to create a job then call `CacheRelationService::singleton()->processChange($this->DataObject)` in the job

# SilverStripe supported module skeleton

A useful skeleton to more easily create a [Silverstripe Module](https://docs.silverstripe.org/en/4/developer_guides/extending/modules/) that conform to the
[Module Standard](https://docs.silverstripe.org/en/developer_guides/extending/modules/#module-standard).

This readme contains descriptions of the parts of this module base you should customise to meet you own module needs.
For example, the module name in the H1 above should be you own module name, and the description text you are reading now
is where you should provide a good short explanation of what your module does.

Where possible we have included default text that can be included as is into your module and indicated in
other places where you need to customise it

Below is a template of the sections of your readme.md you should ideally include to met the Module Standard
and help others make use of your modules.

## License
See [License](license.md)

## Maintainers
 * Adrian Humphreys <adrhumphreys@gmail.com>
 * Chris Penny <cpenny@silverstripe.com>

## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
