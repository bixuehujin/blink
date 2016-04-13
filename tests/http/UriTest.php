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
        $uriString = 'https://user@localhost:8989/foo?q=abc#frag';

        $uri = new Uri(['uriString' => $uriString]);

        $excepted = [
            'scheme' => 'https',
            'host' => 'localhost',
            'port' => 8989,
            'authority' => 'user@localhost:8989',
            'path' => '/foo',
            'query' => 'q=abc',
            'fragment' => 'frag',
        ];

        foreach ($excepted as $key => $value) {
            $this->assertEquals($value, $uri->$key);
        }

        $this->assertEquals($uriString, (string)$uri);
    }
}
