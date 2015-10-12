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

    /**
     * Add a new middleware to the middleware stack of the object.
     *
     * @param $definition
     */
    public function middleware($definition)
    {
        $this->middleware[] = $definition;
    }

    /**
     * Call the middleware stack.
     *
     * @throws InvalidConfigException
     */
    public function callMiddleware()
    {
        foreach ($this->middleware as $definition) {
            $middleware = make($definition);
            if (!$middleware instanceof MiddlewareContract) {
                throw new InvalidConfigException(sprintf("'%s' is not a valid middleware", get_class($middleware)));
            }

            if ($middleware->handle($this) === false) {
                break;
            }
        }
    }
}
