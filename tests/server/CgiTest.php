<?php

namespace blink\tests\server;

use blink\http\Request;
use blink\server\CgiServer;
use blink\tests\TestCase;

/**
 * Class CgiTest
 *
 * @package blink\tests\server
 */
class CgiTest extends TestCase
{
    public function cgiRequests()
    {
        return [
            [ // test with host header
                [
                    'server' => [
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI' => '/path',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                        'HTTP_USER-AGENT' => 'curl/7.43.0',
                        'HTTP_ACCEPT' => '*/*',
                        'HTTP_HOST' => 'localhost:7788',
                    ],
                ],
                [
                    'url' => 'http://localhost:7788/path',
                    'root' => 'http://localhost:7788',
                    'method' => 'GET',
                    'headers' => [
                        'user-agent' => ['curl/7.43.0'],
                        'accept' => ['*/*'],
                        'host' => ['localhost:7788'],
                    ],
                ]
            ],
            [ // test without host header
                [
                    'server' => [
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI' => '/path',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                        'HTTP_USER-AGENT' => 'curl/7.43.0',
                        'HTTP_ACCEPT' => '*/*',
                    ],
                ],
                [
                    'url' => 'http://localhost/path',
                    'root' => 'http://localhost',
                    'method' => 'GET',
                    'headers' => [
                        'user-agent' => ['curl/7.43.0'],
                        'accept' => ['*/*'],
                    ],
                ]
            ],
            [ // test for x-forward-proto
                [
                    'server' => [
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI' => '/path',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                        'HTTP_USER-AGENT' => 'curl/7.43.0',
                        'HTTP_ACCEPT' => '*/*',
                        'HTTP_X_FORWARDED_PROTO' => 'https',
                    ],
                ],
                [
                    'url' => 'https://localhost/path',
                    'root' => 'https://localhost',
                    'method' => 'GET',
                    'headers' => [
                        'user-agent' => ['curl/7.43.0'],
                        'accept' => ['*/*'],
                        'x-forwarded-proto' => ['https'],
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider cgiRequests
     */
    public function testCreateRequestFromSwoole($request, $expects)
    {
        $this->setupGlobals($request);

        /** @var Request $request */
        $request = (new CgiServer())->extractRequest();
        $this->assertEquals($expects['url'], (string)$request->uri);
        $this->assertEquals($expects['url'], (string)$request->url());
        $this->assertEquals($expects['root'], (string)$request->root());
        $this->assertEquals($expects['method'], $request->method());
        $this->assertEquals($expects['headers'], $request->headers->all());
    }

    protected function setupGlobals($reqeust)
    {
        $_SERVER = [];

        foreach($reqeust['server'] as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }
}

