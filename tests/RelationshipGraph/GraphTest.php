<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use ReflectionClass;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\CaredHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedBelongsToModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasManyModel;
use Terraformers\KeysForCache\Tests\Mocks\Models\TouchedHasOneModel;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\ExtendedCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\GlobalCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\NoCachePage;
use Terraformers\KeysForCache\Tests\Mocks\Pages\TouchesPage;

class GraphTest extends SapphireTest
{
    public function testAddGetNode(): void
    {
        $graph = Graph::singleton();
        $graph->flush();

        $reflectionClass = new ReflectionClass(Graph::class);
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
        $graph->flush();

        $reflectionClass = new ReflectionClass(Graph::class);
        $add = $reflectionClass->getMethod('addNode');
        $add->setAccessible(true);
        $get = $reflectionClass->getMethod('getNode');
        $get->setAccessible(true);

        $node = new Node(CachePage::class);

        $add->invoke($graph, $node);

        $this->assertNull($get->invoke($graph, NoCachePage::class));
    }

    public function testAddGetEdge(): void
    {
        $graph = Graph::singleton();
        $graph->flush();

        $reflectionClass = new ReflectionClass(Graph::class);
        $add = $reflectionClass->getMethod('addEdge');
        $add->setAccessible(true);
        $get = $reflectionClass->getMethod('getEdges');
        $get->setAccessible(true);

        $fromNode = new Node(SiteTree::class);
        $to = new Node(CaresPage::class);

        $edge = new Edge($fromNode, $to, 'Parent', 'has_one');

        $add->invoke($graph, $edge);

        /** @var Edge $edge */
        $edges = $get->invoke($graph, SiteTree::class);

        $this->assertCount(1, $edges);

        $edge = array_pop($edges);

        $this->assertInstanceOf(Edge::class, $edge);
        $this->assertEquals(SiteTree::class, $edge->getFromClassName());
        $this->assertEquals(SiteTree::class, $edge->getFromClassName());
    }

    public function testAddGetEdgeNull(): void
    {
        $graph = Graph::singleton();
        $graph->flush();

        $reflectionClass = new ReflectionClass(Graph::class);
        $add = $reflectionClass->getMethod('addEdge');
        $add->setAccessible(true);
        $get = $reflectionClass->getMethod('getEdges');
        $get->setAccessible(true);

        $fromNode = new Node(SiteTree::class);
        $to = new Node(CaresPage::class);

        $edge = new Edge($fromNode, $to, 'Parent', 'has_one');

        $add->invoke($graph, $edge);

        /** @var Edge $edge */
        $edges = $get->invoke($graph, CaresPage::class);

        $this->assertCount(0, $edges);
    }

    public function testFindOrCreateNode(): void
    {
        $graph = Graph::singleton();
        $graph->flush();

        $reflectionClass = new ReflectionClass(Graph::class);
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
        ];
        $expectPageTwoCares = [
            'CaredBelongsToModel' => CaredBelongsToModel::class,
            'CaredHasOneModel' => CaredHasOneModel::class,
            'CaredHasManyModels' => CaredHasManyModel::class,
        ];

        $this->assertEquals($expectPageOneTouch, $pageOneTouch, '', 0.0, 10, true);
        $this->assertEquals([], $pageOneCares, '', 0.0, 10, true);
        $this->assertEquals([], $pageTwoTouch, '', 0.0, 10, true);
        $this->assertEquals($expectPageTwoCares, $pageTwoCares, '', 0.0, 10, true);
    }

    public function testGetEdges(): void
    {
        $graph = Graph::singleton();

        $expected = [
            CaresPage::class,
            ExtendedCaresPage::class,
        ];
        $result = array_map(
            function(Edge $edge) {
                return $edge->getToClassName();
            },
            $graph->getEdges(CaredHasOneModel::class)
        );

        $this->assertEquals($expected, $result, '', 0.0, 10, true);

        $expected = [
            TouchedBelongsToModel::class,
            TouchedHasOneModel::class,
            TouchedHasManyModel::class,
        ];
        $result = array_map(
            function (Edge $edge) {
                return $edge->getToClassName();
            },
            $graph->getEdges(TouchesPage::class)
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
}
