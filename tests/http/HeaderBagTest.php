<?php

namespace blink\tests\http;

use blink\http\HeaderBag;
use blink\tests\TestCase;

class HeaderBagTest extends TestCase
{
    public function testBasic()
    {
        $bag = new HeaderBag([
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ]);

        $this->assertEquals(['application/json'], $bag->get('Content-Type'));
        $this->assertEquals('application/json', $bag->first('Content-Type'));

        $bag->with('Content-Type', 'foo');
        $this->assertEquals(['application/json', 'foo'], $bag->get('Content-Type'));
    }
}
