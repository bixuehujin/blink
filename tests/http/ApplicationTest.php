<?php

namespace blink\tests\http;

use blink\core\BaseObject;
use blink\core\ErrorHandler;
use blink\core\Application;
use blink\http\Request;
use blink\http\Response;
use blink\log\Logger;
use blink\support\Json;
use blink\tests\TestCase;

class ApplicationTest extends TestCase
{
    public function createApplication($boot = true)
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
                    ]);

        if ($boot) {
            $application->bootstrapIfNeeded();
        }

        return $application;
    }

    protected function createRequest($app, $path = '/')
    {
        $request = $app->get('request');
        $request->method = 'GET';
        $request->uri->path = $path;

        return $request;
    }

    public function testSimple()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($this->createRequest($app));
        $this->assertEquals('hello', (string)$response->getBody());
    }

    public function testClosureInjection()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($this->createRequest($app, '/10/plus/20'));
        $this->assertEquals(30, (string)$response->getBody());
    }

    public function testClassInjection()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($request = $this->createRequest($app, '/10/multi/20'));

        $this->assertEquals(200, (string)$response->getBody());
        $this->assertEquals('bar', $request->params->get('foo'));
    }

    public function testGroupRoute()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($request = $this->createRequest($app, '/admin/orders'));

        $this->assertEquals('orders', (string)$response->getBody());
    }

    public function testBootstrapFailure()
    {
        $app = $this->createApplication(false);

        // Add the `/` route again
        $app->route('GET', '/', function () {
            return 'hello';
        });

        $app->bootstrapIfNeeded();

        $response = $app->handleRequest($this->createRequest($app));

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(
            'Cannot register two routes matching "/" for method "GET"',
            Json::decode($response->getBody())['message']
        );
    }
}


class TestController extends BaseObject
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
