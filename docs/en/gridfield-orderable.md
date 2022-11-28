# GridField Orderable support

* [Considerations & Warnings](#considerations--warnings)
* [Applying the Extension](#applying-the-extension)

## Considerations & Warnings

If you are using `symbiote/silverstripe-gridfieldextensions` version `3.5.0` or newer, then you have nothing to worry
about. Full support for all types of `DataObjects` is available.

If you are using a `symbiote/silverstripe-gridfieldextensions` version lower than `3.5.0`, and you're using the
`GridFieldOrderableRows` component:

* `GridFieldOrderableRows` will have out of the box support for KFC for `Versioned` DataObjects, as it already uses the
  ORM and the `write()` method to save sort orders.
* `GridFieldOrderableRows` unfortunately does **not** use the `write()` method for non `Versioned` DataObjects, it
  instead performs raw SQL queries, which completely bypasses the triggers we have attached to `write()`.

There is an open ticket on the GridFieldExtensions module to try and get GridFieldOrderableRows to use the ORM for both
Versioned and non-Versioned DataObjects:
https://github.com/symbiote/silverstripe-gridfieldextensions/issues/335

In the meantime though, we have provided an Extension that adds support for clearing of CacheKeys on non `Versioned`
DataObjects when you are using the GridFieldOrderableRows component.

* `GridFieldOrderableRowsExtension`

This Extension is *not* automatically applied, because I think you should seriously consider Versioning your DataObject.
If you are adding this DataObject to (something like) an Element, which **is** Versioned, then (imo) it is best that all
of the related DataObjects (like its "Items") are also `Versioned`. This gives a consistent author experience - where
they can have draft/live versions of things.

This Extension also doesn't have any test coverage (because of everything we mentioned above). It has only gone through
manual testing. Use at your own risk and be prepared to submit tickets if you find any issues or use cases that aren't
supported.

## Applying the Extension

```yaml
Symbiote\GridFieldExtensions\GridFieldOrderableRows:
  extensions:
    - Terraformers\KeysForCache\Extensions\GridFieldOrderableRowsExtension
```
