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

    public function middleware($definition)
    {
        $this->middleware[] = $definition;
    }

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
