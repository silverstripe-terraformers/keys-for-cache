<?php

namespace Terraformers\KeysForCache\Tests\DataTransferObjects;

use Page;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\DataTransferObjects\EdgeUpdateDto;
use Terraformers\KeysForCache\RelationshipGraph\Edge;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Terraformers\KeysForCache\Tests\Mocks\Pages\NoCachePage;

class EdgeUpdateDtoTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testConstructAndGet(): void
    {
        $page = NoCachePage::create();
        $page->write();
        $nodeFrom = new Node(Page::class);
        $nodeTo = new Node(SiteTree::class);
        $relationship = 'Parent';
        $edge = new Edge($nodeFrom, $nodeTo, $relationship);
        $edgeUpdate = new EdgeUpdateDto($edge, $page);

        $this->assertNotNull($edgeUpdate->getInstance());
        $this->assertNotNull($edgeUpdate->getEdge());
        $this->assertEquals($page->ID, $edgeUpdate->getInstance()->ID);
        $this->assertEquals($edge->getFromClassName(), $edgeUpdate->getEdge()->getFromClassName());
        $this->assertEquals($edge->getToClassName(), $edgeUpdate->getEdge()->getToClassName());
        $this->assertEquals($edge->getRelation(), $edgeUpdate->getEdge()->getRelation());
    }
}
