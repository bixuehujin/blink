<?php

declare(strict_types=1);

namespace blink\routing;

use blink\routing\middleware\MiddlewareStack;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Group
 *
 * @package blink\routing
 */
class Group
{
    use RouterMethods;

    protected Router $router;
    public string    $prefix;
    public array     $routes = [];

    protected MiddlewareStack $stack;

    public function __construct(Router $router, MiddlewareStack $stack, string $prefix)
    {
        $this->router = $router;
        $this->prefix = $prefix;
        $this->stack  = clone $stack;
    }

    public function use(MiddlewareInterface $middleware)
    {
        $this->stack->add($middleware);
    }

    /**
     * Add a new route in the group.
     *
     * @param MiddlewareStack $stack
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    protected function addRoute(MiddlewareStack $stack, array $verbs, string $path, $handler): Route
    {
        return $this->router->addRoute($stack, $verbs, $this->prefix . $path, $handler);
    }
}
