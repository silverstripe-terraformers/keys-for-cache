<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\Node;
use Page;

class NodeTest extends SapphireTest
{
    public function testConstructAndGet(): void
    {
        $node = new Node(Page::class);

        $this->assertEquals(Page::class, $node->getClassName());
    }
}
