<?php

namespace blink\tests\http;

use blink\base\Object;
use blink\base\ErrorHandler;
use blink\Blink;
use blink\http\Application;
use blink\http\Request;
use blink\http\Response;
use blink\log\Logger;
use blink\tests\TestCase;


class ApplicationTest extends TestCase
{
    protected function createApplication()
    {
        $application = Blink::$app = new Application();
        $application
            ->route('GET', '/', function () {
                return 'hello';
            })
            ->route('GET', '/{a}/plus/{b}', function ($a, $b, Request $request) {
                return $a + $b;
            })
            ->route('GET', '/{a}/multi/{b}', 'blink\tests\http\TestController:compute')
            ->bootstrap();

        return $application;
    }

    public function testSimple()
    {
        $request = new Request();
        $response = $this->createApplication()->handleRequest($request);
        $this->assertEquals('"hello"', $response->content());
    }

    public function testClosureInjection()
    {
        $request = new Request(['path' => '/10/plus/20']);
        $response = $this->createApplication()->handleRequest($request);
        $this->assertEquals(30, $response->content());
    }

    public function testClassInjection()
    {
        $request = new Request(['path' => '/10/multi/20']);
        $response = $this->createApplication()->handleRequest($request);
        $this->assertEquals(200, $response->content());
    }
}


class TestController extends Object
{
    public function __construct(Request $request, $config = [])
    {

        parent::__construct($config);
    }
    public function compute($a, $b, Response $response)
    {
        $response->with($a * $b);

        return $response;
    }
}
