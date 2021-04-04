<?php

namespace blink\tests;

use blink\core\Application;

class TestCase extends \blink\testing\TestCase
{
    public function createApplication()
    {
        return new Application([
            'root' => __DIR__,
        ]);
    }
}
