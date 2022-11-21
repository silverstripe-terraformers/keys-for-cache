# Relationship config

Silverstripe already has some rules in place that force us to describe both directions of our relationships, however,
there are some implementations where that is not required by Silverstripe, but **will be** required by this module.

- [`has_one` relationships](#has_one-relationships)
  - [`has_one` <-> `has_many`](#has_one---has_many)
  - [`has_one` <-> `has_one`](#has_one---has_one)
- [`many_many` relationships](#many_many-relationships)
  - [Method one, `care` or `touch` the `many_many` relationship](#method-one-care-or-touch-the-many_many-relationship)
  - [Method two, `care` or `touch` the `has_many` relationship](#method-two-care-or-touch-the-has_many-relationship)

## `has_one` relationships

When you create a `has_one` relationship, Silverstripe does not require you to tell it what the relationship is in the
other direction. When one of your models `care` about one of its `has_one` relationships, then that inforamtion is
critical to us, and you will not be able to proceed without defining it for us.

When you create a `has_one`, then the relationship in the other direction can either be a `has_many`, or a `has_one`.

### `has_one` <-> `has_many`

You've probably seen this many times before. Potentially you haven't needed to  implement it quite as vigurously before
though.

A common example of "missing configuration" is the `Link` module, which states that a `Link` `has_one` `SiteTree`:

```php
private static array $has_one = [
    'SiteTree' => SiteTree::class,
];
```

If you told us that `Link` needs to `care` about `SiteTree`, then we need to understand the relationship **from**
`SiteTree` **to** `Link`. Unfortunately, at the moment we only understand the relationship from `Link` to `SiteTree`.

Since the `SiteTree` class is part of a vendor module, I could add this in an `Extension`:

```php
private static array $has_many = [
    'Links' => Link::class,
];
```

Or through yaml:

```yaml
SilverStripe\CMS\Model\SiteTree:
    has_many:
        Links: gorriecoe\Link\Models\Link
```

We now understand the relationship in both directions, and your `Link` can now `care` about `SiteTree`.

### `has_one` <-> `has_one`

Sometimes when you are creating a `has_one` relationship it is **not** a `has_many` in the other direction. This might
be a Block that has a single (unshared) `Link`.

Silverstripe allows us to define both directions of the relationship here as well with the `belongs_to` configuration.

For example `LinkBlock` `has_one` `Link`:

```php
private static array $has_one = [
    'CtaLink' => Link::class,
];
```

And so we define a `belongs_to` on `Link`:

```php
private static array $belongs_to = [
    'LinkBlock' => LinkBlock::class,
];
```

Our `LinkBlock` can now `care` about `CtaLink`.

## `many_many` relationships

Similarly to the `has_one`, Silverstripe does not force us to define our `many_many` relationships in both directions,
however, it is recommended. https://docs.silverstripe.org/en/4/developer_guides/model/relations/#many-many

A common example of this would be a `Page` that has `many_many` `TaxonomyTerm`:

```php
private static array $many_many = [
    'Terms' => TaxnomyTerm::class,
];
```

If I would like my `Page` to `care` about its `Terms`, then I also need to define the relationship **from**
`TaxonomyTerm` **to** `Page`.

Since the `TaxonomyTerm` class is part of a vendor module, I could add this in an `Extension`:

```php
private static array $belongs_many_many = [
    'Pages' => Page::class,
];
```

Or through yaml:

```yaml
SilverStripe\Taxonomy\TaxonomyTerm:
    belongs_many_many:
        Pages: Page
```

## `many_many` with `through` relationships

If you are using `through` models for your `many_many` relationship, first of all - well done.

### Method one, `care` or `touch` the `many_many` relationship

Your `many_many` should look something like the example below, where we are making a `many_many` between our `Pages`
and `TaxonomyTerms`.

```php
private static array $many_many = [
    'Terms' => [
        'through' => PageTaxonomyTerm::class,
        'from' => 'Parent',
        'to' => 'TaxonomyTerm',
    ],
];
```

You could now decide to `care` about `Terms`.

```php
private static array $cares = [
    'Terms',
];
```

However, like the examples above, we will also need to understand the relationship from `TaxonomyTerms` back to `Page`.
This is something that Silverstripe does not initially force you to do, but we require it.

We could add the relationship info through an extension that we apply to `TaxonomyTerm`. Note, the key things that
changed here are the relationship name (from `Terms` to `Pages`), and the `from` and `to` values are switched, because
we're now traversing the relationship in the oposite direction.

```php
private static array $belongs_many_many = [
    'Pages' => [
        'through' => PageTaxonomyTerm::class,
        'from' => 'TaxonomyTerm',
        'to' => 'Parent',
    ],
];
```

If you were wanting to `touch` rather than `cares`, then you could add this as well.

```php
private static array $touches = [
    'Pages',
];
```

### Method two, `care` or `touch` the `has_many` relationship

If you would like more control, then the example above can be expanded. Instead of only defining your `many_many`, you
can also add a `has_many` to the `through` model, and you could decide to `care` about that instead.

```php
private static array $many_many = [
    'Terms' => [
        'through' => PageTaxonomyTerm::class,
        'from' => 'Parent',
        'to' => 'TaxonomyTerm',
    ],
];

private static array $has_many = [
    'TermRelationships' => PageTaxonomyTerm::class,
];

private static array $cares = [
    'TermRelationships',
];
```

`PageTaxonomyTerm` can then decide what it `cares` or `touches`. Below our `PageTaxonomyTerm` `cares` about the assigned
`TaxonomyTerm`.

```php
class PageTaxonomyTerm extends DataObject
{
    private static string $table_name = 'PageTaxonomyTerm';

    private static array $has_one = [
        'Parent' => Page::class,
        'TaxonomyTerm' => TaxonomyTerm::class,
    ];

    private static array $owned_by = [
        'Parent',
    ];

    private static array $cares = [
        'TaxonomyTerm',
    ];
}
```

Therefore, because `PageTaxonomyTerm` `cares` about the `TaxonomyTerm`, then we also need to define the relationship
from `TaxonomyTerm` to `PageTaxonomyTerm`.

```php
private static array $has_many = [
    'TermRelationships' => PageTaxonomyTerm::class,
];
```
