<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use ReflectionClass;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredManyManyModel;
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
        ];

        $this->assertEquals($expectPageOneTouch, $pageOneTouch, '', 0.0, 10, true);
        $this->assertEquals([], $pageOneCares, '', 0.0, 10, true);
        $this->assertEquals([], $pageTwoTouch, '', 0.0, 10, true);
        $this->assertEquals($expectPageTwoCares, $pageTwoCares, '', 0.0, 10, true);
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

        $this->assertEquals($expected, $result, '', 0.0, 10, true);

        $expected = [
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

        $this->assertEquals($expected, $result, '', 0.0, 10, true);
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
        $cares = $reflectionClass->getProperty('global_cares');
        $cares->setAccessible(true);

        // Reset property values
        $edges->setValue($graph, []);
        $cares->setValue($graph, []);
        // Clear any cache that would have been created during initial instantiation
        $cache = Injector::inst()->get(Graph::CACHE_KEY);
        $cache->clear();

        // Check that we're set up with no cache and no property values
        $this->assertEmpty($edges->getValue($graph));
        $this->assertEmpty($cares->getValue($graph));
        $this->assertFalse($cache->has(Graph::CACHE_KEY_EDGES));
        $this->assertFalse($cache->has(Graph::CACHE_KEY_GLOBAL_CARES));

        // Trigger a flush, which should rebuild our cache and set the property values again
        Graph::singleton()::flush();

        // Check that our edges and global_cares properties have been filled during the flush
        $this->assertNotEmpty($edges->getValue($graph));
        $this->assertNotEmpty($cares->getValue($graph));
        // There should now also be cache values
        $this->assertTrue($cache->has(Graph::CACHE_KEY_EDGES));
        $this->assertTrue($cache->has(Graph::CACHE_KEY_GLOBAL_CARES));
    }

    protected function tearDown(): void
    {
        Injector::inst()->get(Graph::CACHE_KEY)->clear();

        parent::tearDown();
    }
}
