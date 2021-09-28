<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use ReflectionClass;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Graph;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Terraformers\KeysForCache\Tests\Mocks\CachePage;
use Terraformers\KeysForCache\Tests\Mocks\CaresPage;
use Terraformers\KeysForCache\Tests\Mocks\GlobalCaresPage;
use Terraformers\KeysForCache\Tests\Mocks\NoCachePage;
use Terraformers\KeysForCache\Tests\Mocks\TouchesPage;

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

        $edge = new Edge($fromNode, $to, 'Parent');

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

        $edge = new Edge($fromNode, $to, 'Parent');

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

    public function testGetEdges(): void
    {
        $graph = Graph::singleton();

        $expected = [
            CaresPage::class,
        ];
        $result = array_map(
            function(Edge $edge) {
                return $edge->getToClassName();
            },
            $graph->getEdges(SiteTree::class)
        );

        $this->assertEquals($expected, $result, '', 0.0, 10, true);

        $expected = [
            SiteConfig::class,
            SiteTree::class,
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
        $siteTreeClears = $globalCares[SiteTree::class] ?? null;

        $this->assertNotNull($siteConfigClears);
        $this->assertNotNull($siteTreeClears);

        $this->assertCount(1, $siteConfigClears);
        $this->assertCount(1, $siteTreeClears);

        $this->assertEquals(GlobalCaresPage::class, array_pop($siteConfigClears));
        $this->assertEquals(GlobalCaresPage::class, array_pop($siteTreeClears));
    }
}
