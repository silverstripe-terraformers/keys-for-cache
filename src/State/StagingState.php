<?php

namespace Terraformers\KeysForCache\State;

use SilverStripe\Core\Injector\Injectable;

class StagingState
{

    use Injectable;

    private bool $writeEnabled = true;

    private bool $publishEnabled = true;

    public function enableWrite(): void
    {
        $this->writeEnabled = true;
    }

    public function disableWrite(): void
    {
        $this->writeEnabled = false;
    }

    public function canWrite(): bool
    {
        return $this->writeEnabled;
    }

    public function enablePublish(): void
    {
        $this->publishEnabled = true;
    }

    public function disablePublish(): void
    {
        $this->publishEnabled = false;
    }

    public function canPublish(): bool
    {
        return $this->publishEnabled;
    }

}
