<?php

declare(strict_types=1);

namespace blink\di;

use Psr\Container\ContainerInterface;
use blink\core\InvalidParamException;

/**
 * Class Invoker
 *
 * @package blink\di
 */
class Invoker
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Call the given callback or class method with dependency injection.
     *
     * @param $callback
     * @param array $arguments
     * @return mixed
     */
    public function call($callback, $arguments = [])
    {
        $caller = $this->getCallerReflector($callback);

        if (!$caller->isStatic() && is_array($callback) && count($callback) === 2) {
            $callback[0] = $this->container->get($callback[0]);
        }

        $parameters   = $caller->getParameters();
        $dependencies = $this->getMethodDependencies($parameters, $arguments);

        return call_user_func_array($callback, $dependencies);
    }

    protected function getMethodDependencies($parameters, array $arguments = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $dependencies[] = $arguments[$name];
            } elseif ($class = $parameter->getClass()) {
                $dependencies[] = $this->container->get($class->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new InvalidParamException('Missing required argument: ' . $parameter->getName());
            }
        }

        return $dependencies;
    }

    protected function getCallerReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        if (is_array($callback)) {
            return new \ReflectionMethod($callback[0], $callback[1]);
        }

        return new \ReflectionFunction($callback);
    }
}
