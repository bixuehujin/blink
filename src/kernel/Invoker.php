<?php

declare(strict_types=1);

namespace blink\kernel;

use Psr\Container\ContainerInterface;
use blink\core\InvalidParamException;

/**
 * Class Invoker
 *
 * @package blink\kernel
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
        $dependencies = $this->getMethodDependencies($callback, $arguments);

        return call_user_func_array($callback, $dependencies);
    }

    protected function getMethodDependencies($callback, array $arguments = [])
    {
        $dependencies = [];
        $parameters = $this->getCallerReflector($callback)->getParameters();

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

    public function make(string $class, array $arguments)
    {

    }
}
