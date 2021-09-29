<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use Page;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Node;

class EdgeTest extends SapphireTest
{
    public function testConstructAndGet(): void
    {
        $nodeFrom = new Node(Page::class);
        $nodeTo = new Node(SiteTree::class);
        $relationship = 'Parent';

        $edge = new Edge($nodeFrom, $nodeTo, $relationship, 'has_one');

        $this->assertEquals(Page::class, $edge->getFromClassName());
        $this->assertEquals(SiteTree::class, $edge->getToClassName());
        $this->assertEquals('Parent', $edge->getRelation());
        $this->assertEquals('has_one', $edge->getRelationType());
    }
}
