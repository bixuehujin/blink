<?php

declare(strict_types=1);

namespace blink\routing;

use blink\routing\middleware\MiddlewareStack;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Route
 *
 * @package blink\routing
 */
class Route
{
    public array  $verbs;
    public string $path;
    public        $handler;
    public string $name;
    public array  $arguments = [];

    public MiddlewareStack  $stack;

    /**
     * Route constructor.
     *
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     */
    public function __construct(MiddlewareStack $stack, array $verbs, string $path, $handler)
    {
        $this->stack = clone $stack;
        $this->verbs = $verbs;
        $this->path    = $path;
        $this->handler = $handler;
    }

    public function use(MiddlewareInterface $middleware)
    {
        $this->stack->add($middleware);
    }

    /**
     * Sets name of the route.
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $arguments
     * @return Route
     */
    public function withArguments(array $arguments): Route
    {
        $route            = clone $this;
        $route->arguments = $arguments;
        return $route;
    }
}
