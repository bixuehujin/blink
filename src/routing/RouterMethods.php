<?php

declare(strict_types=1);

namespace blink\routing;

/**
 * Trait RouterTrait
 *
 * @package blink\routing
 */
trait RouterMethods
{
    /**
     * Register a GET route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function get(string $path, $handler): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $path, $handler);
    }

    /**
     * Register a POST route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function post(string $path, $handler): Route
    {
        return $this->addRoute(['POST'], $path, $handler);
    }

    /**
     * Register a PUT route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function put(string $path, $handler): Route
    {
        return $this->addRoute(['PUT'], $path, $handler);
    }

    /**
     * Register a PATCH route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function patch(string $path, $handler): Route
    {
        return $this->addRoute(['PATCH'], $path, $handler);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function delete(string $path, $handler): Route
    {
        return $this->addRoute(['DELETE'], $path, $handler);
    }

    /**
     * Register a OPTIONS route.
     *
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function options(string $path, $handler): Route
    {
        return $this->addRoute(['OPTIONS'], $path, $handler);
    }
}
