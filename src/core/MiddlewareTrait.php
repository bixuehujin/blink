<?php


namespace blink\core;

/**
 * Class MiddlewareTrait
 *
 * @package blink\core
 */
trait MiddlewareTrait
{
    public $middleware = [];

    public $freezed = false;

    /**
     * Add a new middleware to the middleware stack of the object.
     *
     * @param $definition
     * @param $prepend
     */
    public function middleware($definition, $prepend = false)
    {
        if ($this->freezed) {
            throw new InvalidCallException('The middleware stack is already called, no middleware can be added');
        }

        if ($prepend) {
            array_unshift($this->middleware, $definition);
        } else {
            $this->middleware[] = $definition;
        }
    }
    
    public function freeze()
    {
        $this->freezed = true;
    }
}
