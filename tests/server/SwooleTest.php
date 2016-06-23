<?php

namespace blink\tests\server;

use blink\core\Object;
use blink\server\SwServer;
use blink\tests\TestCase;

/**
 * Class SwooleTest
 *
 * @package blink\tests\server
 */
class SwooleTest extends TestCase
{

    public function swooleRequest()
    {
        $config = [
            'header' => [
                'host' => 'localhost:7788',
                'user-agent' => 'curl/7.43.0',
                'accept' => '*/*',
            ],
            'server' => [
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
            ],
            'content' => 'body',
        ];

        return new MockedSwServer($config);
    }

    public function testCreateRequestFromSwoole()
    {
        $server = new SwServer();

        $request = $server->createRequest($this->swooleRequest());

        $this->assertEquals('http://localhost:7788/path?a=b', (string)$request->uri);
        $this->assertEquals('GET', $request->method());
        $this->assertEquals('body', (string)$request->getBody());
        $this->assertEquals(['host', 'user-agent', 'accept'], array_keys($request->headers->all()));
    }
}

class MockedSwServer extends Object
{
    public $header;
    public $server;
    public $content;

    public function rawContent()
    {
        return $this->content;
    }
}
