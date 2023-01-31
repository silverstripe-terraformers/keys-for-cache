# Case Studies

* [Project One](#project-one)
  * [General site info](#general-site-info)
  * [Current state of caching](#current-state-of-caching)
  * [Goals](#goals)
  * [KFC implementation](#kfc-implementation)
  * [Results](#results)
    * [DB queries](#db-queries)
    * [Request times](#request-times)
    * [Conclusion](#conclusion)
* [Project Two (TBA)](#project-two--tba-)

# Project One

## General site info

* The site contains roughly ~2,000 published pages.
* Pages contain on average 4 (Elemental) blocks each.
* Pages contain other "non-block" elements, like banners, menus, footer, etc.
* There is a good mixture of block types (14), including multiple "complex" blocks where there is multiple levels of
  nested dependencies (EG: Carousel has Item relationships, Items have Image and Linkable relationships).
* The primary navigation includes nesting to all levels of the SiteTree (which is why we're going to see a high
  numbers of DB queries on un-cached requests).
  * Optimising the Menu is not a concern of this case study, but we do hope to see some improvements from switching
  to KFC.

## Current state of caching

* Project One suffers from some of the pain points that KFC attempts to solve.
  * Some cache keys are a bit complicated and might be difficult for new devs to get up to speed with.
  * Models with high levels of nesting have cache keys that do not invalidate at all desired times.
    * EG: The "Carousel problem". A Carousel has Items, Items have Links and Images, and it's far too costly to
      calculate a cache key for a Carousel that includes the appropriate Images/Links/etc.
  * Some cache keys might invalidate too often.
    * EG: They might invalidate when a global event happens, like "invalidate all Menus when any Linkable model updates"
      (not only Links that are relevant to the Menu).
  * The site has a relatively high number of DB queries for every un-cached request (noted in "General site info").
    * The number of DB queries is currently a bottleneck for Project One.
* The site already has a pretty balanced caching policy. That being, the cost of calculating a key is weighed against
  the value (EG: the "Carousel problem", where the solution for us was to not include Images/Links in the cache key).
  * I do not believe the cost of calculating any single cache key for the site is unreasonable.

## Goals

* Improve management of cache keys for developers.
* Improve the specificity of our cache keys (EG: solving the "Carousel problem").
* Given that the site already has relatively performant cache keys, we're not expecting any huge performance
  improvements, but...
  * We would like to see some reduction in the number of DB queries, and
  * Ideally we want no negative impact to load times.

## KFC implementation

Implementation of KFC to the project included:

* Reviewing any/all methods found in Page models and controllers related to providing cache keys.
  * Replacing the use of these methods with `cares` and/or `global_cares` considerations on the appropriate page
    classes and relationships.
  * Adding a "global" key to `SiteConfig` that contains a `global_cares` for `SiteTree`. This is discussed a bit in
    [Headers, Footers, and other "global" content areas](examples.md#headers-footers-and-other--global--content-areas).
* Reviewing any/all methods found in (Elemental) blocks related to providing cache keys.
  * Replacing the use of these methods with `cares` on appropriate block relationships.

Implementaiton took (roughly) 40 minutes.

## Results

These are not the most comprehensive tests, but we believe they show enough.

### DB queries

Installed [Lekoala/Debugbar](https://github.com/lekoala/silverstripe-debugbar) so that we can see the number of DB
queries we are making on each page request.

* "1st" requests were made as a separate request after a `?flush=1`, so there is no active partial cache. Generating the
  cache would also be part of this request.
* "2nd" requests were a fast follow request where a partial cache is now available.

**Original implementation:**

* 1st: 1,430 queries
* 2nd: 96 queries

**KFC implementation:**

* 1st: 1,219 queries
* 2nd: 73 queries

**Outcome:** Some positive improvements to DB queries for both the fresh request and the request with partial caches
available.

### Request times

This test was performed by running a basic Siege test:

```bash
siege -d1 -r200 -c4 --no-parser http://project-one.local/
```

**Original implementation:**

```
Elapsed time:               163.82 secs
Response time:              0.28 secs
Transaction rate:           4.88 trans/sec
Throughput:                 0.61 MB/sec
Concurrency:                1.35
Longest transaction:        5.30
Shortest transaction:       0.18
```

**KFC implementation:**

```
Elapsed time:               162.95 secs
Response time:              0.26 secs
Transaction rate:           4.91 trans/sec
Throughput:                 0.61 MB/sec
Concurrency:                1.30
Longest transaction:        4.98
Shortest transaction:       0.17
```

**Outcome:** I would say that the difference here is negligable, but there is a slight improvement when using KFC.
Overall, I think this meets expectations, given that we knew that Project One already had performant cached keys.

### Conclusion

Overall, considering ~40 minutes of effort to upgrade the project to using KFC, I would say that the outcomes for both
DB queries and request times are pretty great.

# Project Two (TBA)

TBA
