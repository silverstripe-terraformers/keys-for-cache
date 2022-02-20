<?php

namespace Terraformers\KeysForCache\Tests\RelationshipGraph;

use SilverStripe\Dev\SapphireTest;
use Terraformers\KeysForCache\RelationshipGraph\GraphBuildException;

class GraphBuildExceptionTest extends SapphireTest
{
    public function testErrorsConvertedToMessage(): void
    {
        $errors = [
            'Error 1',
            'Error 2',
            'Error 3',
        ];

        $this->expectExceptionMessage(sprintf("\r\n%s", implode("\r\n", $errors)));

        throw new GraphBuildException($errors);
    }
}
