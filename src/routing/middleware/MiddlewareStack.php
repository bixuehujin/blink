<?php

namespace blink\routing\middleware;

use blink\routing\middleware\MiddlewareHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class MiddlewareStack
 *
 * @package blink\routing\middleware
 */
class MiddlewareStack implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    protected array $chain = [];

    protected RequestHandlerInterface $defaultHandler;

    public function setDefaultHandler(RequestHandlerInterface $handler)
    {
        $this->defaultHandler = $handler;
    }

    /**
     * Adds middleware to the chain.
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function add(MiddlewareInterface $middleware): self
    {
        $this->chain[] = $middleware;
        return $this;
    }

    /**
     * Builds the request handler chain.
     *
     * @return RequestHandlerInterface
     */
    protected function buildStack(): RequestHandlerInterface
    {
        $chain = $this->defaultHandler;

        foreach (array_reverse($this->chain) as $middleware) {
            $chain = $this->wrap($middleware, $chain);
        }

        return $chain;
    }

    protected function wrap(MiddlewareInterface $middleware, RequestHandlerInterface $handler)
    {
        return new MiddlewareHandler($middleware, $handler);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $stack = $this->buildStack();

        return $stack->handle($request);
    }
}
