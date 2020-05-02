<?php

namespace blink\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class MiddlewareChain
 *
 * @package blink\http
 */
class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    protected array $chain = [];

    protected ?RequestHandlerInterface $defaultHandler = null;

    public function __construct(?RequestHandlerInterface $defaultHandler = null)
    {
        if ($defaultHandler === null) {
            $defaultHandler = null;
        }

        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Adds middleware to the chain.
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function add(MiddlewareInterface $middleware): static
    {
        $this->chain[] = $middleware;
        return $this;
    }

    /**
     * Builds the request handler chain.
     *
     * @return RequestHandlerInterface
     */
    protected function buildChain(): RequestHandlerInterface
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
        $chain = $this->buildChain();

        return $chain->handle($request);
    }
}
