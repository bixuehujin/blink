<?php

namespace blink\tests\http;

use blink\http\Uri;
use blink\tests\TestCase;

/**
 * Class UriTest
 *
 * @package blink\tests\http
 */
class UriTest extends TestCase
{
    public function testBasic()
    {
        $uriString = 'https://user:pass@localhost:8989/foo?q=abc#frag';

        $uri = new Uri($uriString);

        $excepted = [
            'scheme' => 'https',
            'host' => 'localhost',
            'port' => 8989,
            'authority' => 'user:pass@localhost:8989',
            'path' => '/foo',
            'query' => 'q=abc',
            'fragment' => 'frag',
        ];

        foreach ($excepted as $key => $value) {
            $this->assertEquals($value, $uri->$key);
        }

        $this->assertEquals($uriString, (string)$uri);
    }

    public function testToString()
    {
        $url = 'https://user:pass@localhost:8989/foo?q=abc#frag';
        $uri = new Uri($url);
        $this->assertEquals($url, (string) $uri);
    }

    public function testUtf8Uri()
    {
        $uri = new Uri('http://世界.中国/foobar');

        $this->assertEquals('世界.中国', $uri->getHost());
    }
}
