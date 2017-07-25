<?php

namespace blink\tests\http;

use blink\http\HeaderBag;
use blink\http\ParamBag;
use blink\http\Request;
use blink\tests\TestCase;

class RequestTest extends TestCase
{
    public function testDefault()
    {
        $request = new Request([]);

        $this->assertInstanceOf(ParamBag::class, $request->params);
        $this->assertInstanceOf(HeaderBag::class, $request->headers);
        $this->assertInstanceOf(ParamBag::class, $request->body);

        $this->assertEquals('GET', $request->method());
        $this->assertEquals('localhost', $request->host());
        $this->assertEquals('http://localhost/', $request->url());
    }

    public function testBasic()
    {
        $request = new Request([
            'method' => 'POST',
            'queryString' => 'a=b&b=c',
            'content' => json_encode(['foo' => 'bar']),
            'headers' => [
                'Content-Type' => 'application/json; Charset=utf8',
                'x-forwarded-proto' => 'https',
                'x-forwarded-port' => 443
            ]
        ]);

        $this->assertTrue($request->is('post'));
        $this->assertEquals(['a' => 'b', 'b' => 'c'], $request->params->all());
        $this->assertEquals(['foo' => 'bar'], $request->body->all());

        $this->assertEquals('b', $request->input('a'));
        $this->assertEquals(true, $request->has('foo'));
        $this->assertEquals(true, $request->secure());
    }

    public function testCookies()
    {
        $request = new Request([
            'cookies' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertEquals('foo', $request->cookies->get('foo')->name);
        $this->assertEquals('bar', $request->cookies->get('foo')->value);
    }
}
