<?php

declare(strict_types=1);

namespace blink\routing;

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

    /**
     * Route constructor.
     *
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     */
    public function __construct(array $verbs, string $path, $handler)
    {
        $this->verbs   = $verbs;
        $this->path    = $path;
        $this->handler = $handler;
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
        $route = clone $this;
        $route->arguments = $arguments;
        return $route;
    }
}
