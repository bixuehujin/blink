<?php

namespace blink\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class MiddlewareHandler
 *
 * @package blink\http
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    private MiddlewareInterface $middleware ;

    private RequestHandlerInterface $handler;

    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $requestHandler)
    {
        $this->middleware = $middleware;
        $this->handler = $requestHandler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->handler);
    }
}
