<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use Page;
use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Node;

class NodeTest extends SapphireTest
{
    public function testConstructAndGet(): void
    {
        $node = new Node(Page::class);

        $this->assertEquals(Page::class, $node->getClassName());
    }
}
