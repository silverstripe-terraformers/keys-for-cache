<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use ReflectionClass;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\Models\CacheKey;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicCaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\PolymorphicTouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedManyManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\GlobalCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\NoCachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;
use Terraformers\KeysForCache\Tests\Mocks\Relations\CaresPageCaredThroughModel;
use Terraformers\KeysForCache\Tests\Mocks\Relations\TouchesPageTouchedThroughModel;

class GraphTest extends SapphireTest
{
    public function testAddGetNode(): void
    {
        $graph = Graph::singleton();

        // Need to use ReflectionClass as the properties and methods we want to test are private
        $reflectionClass = new ReflectionClass(Graph::class);

        // Need to flush these properties so that we can explicitly test these methods
        $property = $reflectionClass->getProperty('nodes');
        $property->setAccessible(true);
        $property->setValue($graph, []);

        // Set accessible for the methods we are testing
        $add = $reflectionClass->getMethod('addNode');
        $add->setAccessible(true);
        $get = $reflectionClass->getMethod('getNode');
        $get->setAccessible(true);

        $node = new Node(CachePage::class);

        $add->invoke($graph, $node);

        $node = $get->invoke($graph, CachePage::class);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(CachePage::class, $node->getClassName());
    }

    public function testAddGetNodeNull(): void
    {
        $graph = Graph::singleton();

        // Need to use ReflectionClass as the properties and methods we want to test are private
        $reflectionClass = new ReflectionClass(Graph::class);

        // Need to flush these properties so that we can explicitly test these methods
        $property = $reflectionClass->getProperty('nodes');
        $property->setAccessible(true);
        $property->setValue($graph, []);

        // Set accessible for the methods we are testing
        $add = $reflectionClass->getMethod('addNode');
        $add->setAccessible(true);
        $get = $reflectionClass->getMethod('getNode');
        $get->setAccessible(true);

        $node = new Node(CachePage::class);

        $add->invoke($graph, $node);

        $this->assertNull($get->invoke($graph, NoCachePage::class));
    }

    public function testFindOrCreateNode(): void
    {
        $graph = Graph::singleton();

        // Need to use ReflectionClass as the properties and methods we want to test are private
        $reflectionClass = new ReflectionClass(Graph::class);

        // Need to flush these properties so that we can explicitly test these methods
        $property = $reflectionClass->getProperty('nodes');
        $property->setAccessible(true);
        $property->setValue($graph, []);

        // Set accessible for the methods we are testing
        $find = $reflectionClass->getMethod('findOrCreateNode');
        $find->setAccessible(true);
        $get = $reflectionClass->getMethod('getNode');
        $get->setAccessible(true);

        $find->invoke($graph, CachePage::class);

        $node = $get->invoke($graph, CachePage::class);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(CachePage::class, $node->getClassName());
    }

    public function testGetClassAndRelation(): void
    {
        $graph = Graph::singleton();

        $reflectionClass = new ReflectionClass(Graph::class);
        $method = $reflectionClass->getMethod('getClassAndRelation');
        $method->setAccessible(true);

        $single = CachePage::class;
        $both = sprintf('%s.Relationship', CachePage::class);

        [$className, $relationship] = $method->invoke($graph, $single);

        $this->assertEquals(CachePage::class, $className);
        $this->assertNull($relationship);

        [$className, $relationship] = $method->invoke($graph, $both);

        $this->assertEquals(CachePage::class, $className);
        $this->assertEquals('Relationship', $relationship);
    }

    public function testGetRelationshipConfig(): void
    {
        $graph = Graph::singleton();

        $reflectionClass = new ReflectionClass(Graph::class);
        $method = $reflectionClass->getMethod('getRelationshipConfig');
        $method->setAccessible(true);

        $pageOne = TouchesPage::config();
        $pageTwo = CaresPage::config();

        $pageOneTouch = $method->invoke($graph, $pageOne->get('touches'), $pageOne);
        $pageOneCares = $method->invoke($graph, $pageOne->get('cares'), $pageOne);
        $pageTwoTouch = $method->invoke($graph, $pageTwo->get('touches'), $pageTwo);
        $pageTwoCares = $method->invoke($graph, $pageTwo->get('cares'), $pageTwo);

        $expectPageOneTouch = [
            'PolymorphicHasOne' => DataObject::class,
            'PolymorphicTouchedHasManyModels' => PolymorphicTouchedHasManyModel::class . '.PolymorphicHasOne',
            'TouchedBelongsToModel' => TouchedBelongsToModel::class,
            'TouchedHasOneModel' => TouchedHasOneModel::class,
            'TouchedHasManyModels' => TouchedHasManyModel::class,
            'TouchedManyManyModels' => TouchedManyManyModel::class,
            'TouchedThroughModels' => [
                'through' => TouchesPageTouchedThroughModel::class,
                'from' => 'Parent',
                'to' => 'TouchedThroughModel',
            ],
        ];
        $expectPageTwoCares = [
            'CaredBelongsToModel' => CaredBelongsToModel::class,
            'CaredHasOneModel' => CaredHasOneModel::class,
            'CaredHasManyModels' => CaredHasManyModel::class,
            'CaredManyManyModels' => CaredManyManyModel::class,
            'CaredThroughModels' => [
                'through' => CaresPageCaredThroughModel::class,
                'from' => 'Parent',
                'to' => 'CaredThroughModel',
            ],
            'PolymorphicHasOne' => DataObject::class,
            'PolymorphicCaredHasManyModels' => PolymorphicCaredHasManyModel::class . '.PolymorphicHasOne',
        ];

        $this->assertEqualsCanonicalizing($expectPageOneTouch, $pageOneTouch);
        $this->assertEqualsCanonicalizing([], $pageOneCares);
        $this->assertEqualsCanonicalizing([], $pageTwoTouch);
        $this->assertEqualsCanonicalizing($expectPageTwoCares, $pageTwoCares);
    }

    public function testGetEdgesFrom(): void
    {
        $graph = Graph::singleton();

        $expected = [
            CaresPage::class,
            ExtendedCaresPage::class,
        ];
        $result = array_map(
            static function (Edge $edge) {
                return $edge->getToClassName();
            },
            $graph->getEdgesFrom(CaredHasOneModel::class)
        );

        $this->assertEqualsCanonicalizing($expected, $result);

        $expected = [
            DataObject::class,
            PolymorphicTouchedHasManyModel::class,
            TouchedBelongsToModel::class,
            TouchedHasOneModel::class,
            TouchedHasManyModel::class,
            TouchedManyManyModel::class,
            TouchedThroughModel::class,
        ];
        $result = array_map(
            static function (Edge $edge) {
                return $edge->getToClassName();
            },
            $graph->getEdgesFrom(TouchesPage::class)
        );

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testGetGlobalCares(): void
    {
        $graph = Graph::singleton();
        $globalCares = $graph->getGlobalCares();

        $this->assertCount(2, $globalCares);

        $siteConfigClears = $globalCares[SiteConfig::class] ?? null;
        $cachePageClears = $globalCares[CachePage::class] ?? null;

        $this->assertNotNull($siteConfigClears);
        $this->assertNotNull($cachePageClears);

        $this->assertCount(1, $siteConfigClears);
        $this->assertCount(1, $cachePageClears);

        $this->assertEquals(GlobalCaresPage::class, array_pop($siteConfigClears));
        $this->assertEquals(GlobalCaresPage::class, array_pop($cachePageClears));
    }

    public function testCacheSetOnFlush(): void
    {
        $graph = Graph::singleton();
        // Using ReflectionClass so that we can reset and test that these properties are populated as we expect
        $reflectionClass = new ReflectionClass(Graph::class);
        $edges = $reflectionClass->getProperty('edges');
        $edges->setAccessible(true);
        $globalCares = $reflectionClass->getProperty('global_cares');
        $globalCares->setAccessible(true);

        // Reset property values
        $edges->setValue($graph, []);
        $globalCares->setValue($graph, []);
        // Clear any cache that would have been created during initial instantiation
        $cache = Injector::inst()->get(Graph::CACHE_KEY);
        $cache->clear();

        // Check that we're set up with no cache and no property values
        $this->assertEmpty($edges->getValue($graph));
        $this->assertEmpty($globalCares->getValue($graph));
        $this->assertFalse($cache->has(Graph::CACHE_KEY_EDGES));
        $this->assertFalse($cache->has(Graph::CACHE_KEY_GLOBAL_CARES));

        // Trigger a flush, which should rebuild our cache and set the property values again
        $graph::flush();

        // Check that our edges and global_cares properties have been filled during the flush
        $this->assertNotEmpty($edges->getValue($graph));
        $this->assertNotEmpty($globalCares->getValue($graph));
        // There should now also be cache values
        $this->assertTrue($cache->has(Graph::CACHE_KEY_EDGES));
        $this->assertTrue($cache->has(Graph::CACHE_KEY_GLOBAL_CARES));
    }

    public function testBuildFromCache(): void
    {
        // Instantiating the singleton will build
        $graph = Graph::singleton();
        // Using ReflectionClass so that we can reset and test that these properties are populated as we expect
        $reflectionClass = new ReflectionClass(Graph::class);
        $edges = $reflectionClass->getProperty('edges');
        $edges->setAccessible(true);
        $globalCares = $reflectionClass->getProperty('global_cares');
        $globalCares->setAccessible(true);
        $buildEdges = $reflectionClass->getMethod('buildEdges');
        $buildEdges->setAccessible(true);
        $buildGlobalCares = $reflectionClass->getMethod('buildGlobalCares');
        $buildGlobalCares->setAccessible(true);

        $edgesCount = count($edges->getValue($graph));
        $globalCaresCount = count($globalCares->getValue($graph));

        // Reset property values
        $edges->setValue($graph, []);
        $globalCares->setValue($graph, []);
        // Fetch our cache
        $cache = Injector::inst()->get(Graph::CACHE_KEY);

        // Check that we're set up with a cache but with no property values
        $this->assertEmpty($edges->getValue($graph));
        $this->assertEmpty($globalCares->getValue($graph));
        $this->assertTrue($cache->has(Graph::CACHE_KEY_EDGES));
        $this->assertTrue($cache->has(Graph::CACHE_KEY_GLOBAL_CARES));

        // Let's now change some of our configuration from what is in the cache. The next time we trigger a build
        // (without a flush) we should still have our expected Graph (because our cache won't have updated)
        // Below we have updated cares/touches to only have one relationship, and we have added a global_cares
        CaresPage::config()
            ->set(
                'cares',
                [
                    'CaredHasOneModel',
                ]
            )
            ->set(
                'global_cares',
                [
                    SiteTree::class,
                ]
            );

        $buildEdges->invoke($graph);
        $buildGlobalCares->invoke($graph);

        // Check that our global cares are still the same as our cache
        $this->assertCount($globalCaresCount, $globalCares->getValue($graph));
        // There should be no global_cares on SiteTree at the moment
        $this->assertArrayNotHasKey(SiteTree::class, $globalCares->getValue($graph));

        // Check that our edges are still the same as our cache
        $this->assertCount($edgesCount, $edges->getValue($graph));
        // CaredBelongsToModel was removed in our config change, but we expect it to be present from our cache rebuild
        // There should be two Edges for this class
        $this->assertCount(2, $graph->getEdgesFrom(CaredBelongsToModel::class));

        // Now lets flush our cache and rebuild, we should end up with a different graph
        $graph::flush();

        // Check that our global cares have changed
        $this->assertNotCount($globalCaresCount, $globalCares->getValue($graph));
        $values = $globalCares->getValue($graph);
        // CaresPage now has a global_cares for SiteTree, so it should now be present
        $this->assertArrayHasKey(SiteTree::class, $values);
        $this->assertEqualsCanonicalizing(
            [
                CaresPage::class,
                ExtendedCaresPage::class,
            ],
            $values[SiteTree::class],
        );

        // Check that our Edges have changed
        $this->assertNotCount($edgesCount, $edges->getValue($graph));
        // CaredBelongsToModel should no longer be represented
        $this->assertCount(0, $graph->getEdgesFrom(CaredBelongsToModel::class));
    }

    public function testGetValidClasses(): void
    {
        $graph = Graph::singleton();
        $reflectionClass = new ReflectionClass(Graph::class);
        $getValidClasses = $reflectionClass->getMethod('getValidClasses');
        $getValidClasses->setAccessible(true);

        $validClasses = $getValidClasses->invoke($graph);
        $ignoreList = CacheKey::config()->get('ignorelist');
        $intersect = array_intersect($validClasses, $ignoreList);

        // validClasses shouldn't contain any value from ignore list
        $this->assertCount(0, $intersect);
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
