<?php

namespace blink\tests\http;

use blink\http\ParamBag;
use blink\tests\TestCase;

class ParamBagTest extends TestCase
{
    public function testGetBoolean()
    {
        $bag = new ParamBag(['foo' => 'true', 'bar' => 'TRUE']);
        $this->assertTrue($bag->boolean('foo'));
        $this->assertTrue($bag->boolean('bar'));

        $this->assertEquals(['bar' => 'TRUE'], $bag->only(['bar']));
    }
}
