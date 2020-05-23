<?php

declare(strict_types=1);

namespace blink\tests\routing;

use blink\http\Request;
use blink\http\Response;
use blink\di\Container;
use blink\routing\exceptions\MethodNotAllowedException;
use blink\routing\exceptions\RouteNotFoundException;
use blink\routing\Group;
use blink\routing\middleware\ErrorCatcher;
use blink\routing\Router;
use blink\tests\TestCase;

/**
 * Class RouterTest
 *
 * @package blink\tests\routing
 */
class RouterTest extends TestCase
{
    protected function createRouter(): Router
    {
        $router = new Router();

        $router->get('/foo', 'foo_handler');
        $router->get('/foo/{arg}', 'foo_handler_with_arg');

        $router->group('/rethink', function (Group $group) {
            $group->get('/php', 'rethink_handler_php');
            $group->get('/php/{arg}', 'rethink_handler_php_with_arg');
        });

        return $router;
    }

    public function routingCases()
    {
        return [
            [
                'GET',
                '/foo',
                'foo_handler',
                [],
            ],
            [
                'GET',
                '/foo/value',
                'foo_handler_with_arg',
                ['arg' => 'value'],
            ],
            [
                'GET',
                '/rethink/php',
                'rethink_handler_php',
                [],
            ],
            [
                'GET',
                '/rethink/php/value',
                'rethink_handler_php_with_arg',
                ['arg' => 'value'],
            ],
        ];
    }

    /**
     * @dataProvider routingCases
     */
    public function testRouting($verb, $path, $handler, $args)
    {
        $router = $this->createRouter();
        $route  = $router->dispatch($verb, $path);
        $this->assertEquals($handler, $route->handler);
        $this->assertEquals($args, $route->arguments);
    }

    public function testRouteNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route not found, no route to /not-found-path');

        $router = $this->createRouter();
        $router->dispatch('GET', '/not-found-path');
    }

    public function testMethodNotAllowed()
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage("POST method is not allowed for /foo");

        $router = $this->createRouter();
        $router->dispatch('POST', '/foo');
    }

    public function testHandleRequest()
    {
        $router = new Router();
        $router->setContainer(new Container());

        $router->get('/foo/{name}', function (string $name) {
            $resp = new Response();

            return $resp->with('Hello ' . $name);
        });

        $request = new Request([
            'method' => 'GET',
            'uri' => '/foo/world',
        ]);

        $response = $router->handle($request);
        $this->assertEquals('Hello world', $response->data);
    }

    public function testHandleRequestWithGlobalMiddleware()
    {
        $router = new Router();
        $router->setContainer(new Container());
        $router->use(new ErrorCatcher());

        $request = new Request([
            'method' => 'GET',
            'uri' => '/foo/world',
        ]);

        $response = $router->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandleRequestWithGroupMiddleware()
    {
        $router = new Router();
        $router->setContainer(new Container());
        $router->group('/rethink', function (Group $router) {
            $router->use(new ErrorCatcher());
            $router->get("/foo", function () {
                throw new \Exception("A error occured");
            });
        });

        $request = new Request([
            'method' => 'GET',
            'uri' => '/rethink/foo',
        ]);

        $response = $router->handle($request);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testHandleRequestWithRouteMiddleware()
    {
        $router = new Router();
        $router->setContainer(new Container());
        $router->group('/rethink', function (Group $router) {
            $router
                ->get("/foo", function () {
                    throw new \Exception("A error occured");
                })
                ->use(new ErrorCatcher())
            ;
        });

        $request = new Request([
            'method' => 'GET',
            'uri' => '/rethink/foo',
        ]);

        $response = $router->handle($request);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
