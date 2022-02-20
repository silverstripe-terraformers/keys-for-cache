<?php

namespace Terraformers\KeysForCache\RelationshipGraph;

use Exception;
use Throwable;

class GraphBuildException extends Exception
{
    public function __construct(array $errors, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf("\r\n%s", implode("\r\n", $errors));

        parent::__construct($message, $code, $previous);
    }
}
