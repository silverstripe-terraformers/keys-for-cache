<?php

namespace Terraformers\KeysForCache;

use SilverStripe\ORM\DataObject;

class EdgeUpdateDTO
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

