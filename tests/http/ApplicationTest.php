<?php

namespace blink\tests\http;

use blink\core\Object;
use blink\core\ErrorHandler;
use blink\core\Application;
use blink\http\Request;
use blink\http\Response;
use blink\log\Logger;
use blink\tests\TestCase;

class ApplicationTest extends TestCase
{
    protected function createApplication()
    {
        $application = new Application(['root' => '.']);
        $application->route('GET', '/', function () {
            return 'hello';
        })
                    ->route('GET', '/{a}/plus/{b}', function ($a, $b, Request $request) {
                        return $a + $b;
                    })
                    ->route('GET', '/{a}/multi/{b}', 'blink\tests\http\TestController@compute')
                    ->group('/admin', [
                        [
                            'GET',
                            '/orders',
                            function () {
                                return 'orders';
                            }
                        ]
                    ])
                    ->bootstrapIfNeeded();

        return $application;
    }

    protected function createRequest($app, $path = '/')
    {
        $request = $app->get('request');
        $request->path = $path;

        return $request;
    }

    public function testSimple()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($this->createRequest($app));
        $this->assertEquals('hello', $response->content());
    }

    public function testClosureInjection()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($this->createRequest($app, '/10/plus/20'));
        $this->assertEquals(30, $response->content());
    }

    public function testClassInjection()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($request = $this->createRequest($app, '/10/multi/20'));

        $this->assertEquals(200, $response->content());
        $this->assertEquals('bar', $request->params->get('foo'));
    }

    public function testGroupRoute()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($request = $this->createRequest($app, '/admin/orders'));

        $this->assertEquals('orders', $response->content());
    }
}


class TestController extends Object
{
    public function __construct(Request $request, $config = [])
    {
        $request->params->set('foo', 'bar');

        parent::__construct($config);
    }

    public function compute($a, $b, Response $response)
    {
        $response->with($a * $b);

        return $response;
    }
}
