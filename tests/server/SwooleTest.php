<?php

namespace blink\tests\server;

use blink\http\Request;
use blink\server\SwServer;
use blink\tests\TestCase;

/**
 * Class SwooleTest
 *
 * @package blink\tests\server
 */
class SwooleTest extends TestCase
{
    public function swooleRequests()
    {
        $server = [
            'request_method' => 'GET',
            'query_string' => 'a=b',
            'request_uri' => '/path',
            'path_info' => '/path',
            'request_time' => 1466694566,
            'server_port' => 7788,
            'remote_port' => 55234,
            'remote_addr' => '127.0.0.1',
            'server_protocol' => 'HTTP/1.1',
            'server_software' => 'swoole-http-server',
        ];

        return [
            [
                [ // request with proper header
                    'header' => [
                        'host' => 'localhost:7788',
                        'user-agent' => 'curl/7.43.0',
                        'accept' => '*/*',
                    ],
                    'server' => $server,
                    'content' => 'body',
                ],
                [
                    'url' => 'http://localhost:7788/path?a=b',
                    'root' => 'http://localhost:7788',
                    'method' => 'GET',
                    'body' => 'body',
                    'headers' => [
                        'host' => ['localhost:7788'],
                        'user-agent' => ['curl/7.43.0'],
                        'accept' => ['*/*'],
                    ],
                ]
            ],
            [
                [ // request with empty header
                    'header' => [],
                    'server' => $server,
                    'content' => 'body',
                ],
                [
                    'url' => 'http://0.0.0.0:7788/path?a=b',
                    'root' => 'http://0.0.0.0:7788',
                    'method' => 'GET',
                    'body' => 'body',
                    'headers' => [],
                ]
            ],
            [
                [ // request with x-forwarded-proto
                    'header' => [
                        'x-forwarded-proto' => 'https',
                        'host' => 'rethinkphp.com',
                    ],
                    'server' => $server,
                    'content' => 'body',
                ],
                [
                    'url' => 'https://rethinkphp.com/path?a=b',
                    'root' => 'https://rethinkphp.com',
                    'method' => 'GET',
                    'body' => 'body',
                    'headers' => [
                        'x-forwarded-proto' => ['https'],
                        'host' => ['rethinkphp.com'],
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider swooleRequests
     */
    public function testCreateRequestFromSwoole($request, $expects)
    {
        $server = new SwServer();

        /** @var Request $request */
        $request = $server->createRequest(new MockedSwServer($request));
        $this->assertEquals($expects['url'], (string)$request->uri);
        $this->assertEquals($expects['url'], (string)$request->url());
        $this->assertEquals($expects['root'], (string)$request->root());
        $this->assertEquals($expects['method'], $request->method());
        $this->assertEquals($expects['body'], (string)$request->getBody());
        $this->assertEquals($expects['headers'], $request->headers->all());
    }
}

class MockedSwServer extends SwServer
{
    public $header;
    public $server;
    public $content;

    public function rawContent()
    {
        return $this->content;
    }
}
