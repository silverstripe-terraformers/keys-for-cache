<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\RelationshipGraph\Edge;

class EdgeUpdateDto
{

    public function __construct(private readonly Edge $edge, private readonly DataObject $instance)
    {
    }

    public function getEdge(): Edge
    {
        return $this->edge;
    }

    public function getInstance(): DataObject
    {
        return $this->instance;
    }
}
