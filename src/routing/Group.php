<?php

declare(strict_types=1);

namespace blink\routing;

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

    public function __construct(Router $router, string $prefix)
    {
        $this->router = $router;
        $this->prefix = $prefix;
    }

    /**
     * Add a new route in the group.
     *
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    protected function addRoute(array $verbs, string $path, $handler): Route
    {
        return $this->router->addRoute($verbs, $this->prefix . $path, $handler);
    }
}
