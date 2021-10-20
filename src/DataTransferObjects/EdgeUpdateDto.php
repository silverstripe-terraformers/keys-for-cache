<?php

namespace Terraformers\KeysForCache\DataTransferObjects;

use SilverStripe\ORM\DataObject;
use Terraformers\KeysForCache\RelationshipGraph\Edge;

class EdgeUpdateDto
{
    private Edge $edge;
    private DataObject $instance;

    public function __construct(Edge $edge, DataObject $instance)
    {
        $this->edge = $edge;
        $this->instance = $instance;
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

