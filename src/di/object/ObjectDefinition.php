<?php

namespace blink\di\object;

use Closure;
use blink\di\Reference;

/**
 * ObjectDefinition represents the definition to create objects.
 *
 * @package blink\di\object
 */
class ObjectDefinition
{
    protected string    $className;
    protected ?Closure  $factory     = null;
    protected ?Method   $constructor = null;
    /**
     * @var Reference[]
     */
    protected array  $properties = [];

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function name(): string
    {
        return $this->className;
    }

    public function haveFactory(callable $factory)
    {
        $this->factory = $factory;
    }

    public function haveConstructor(?callable $callback = null): Method
    {
        $this->constructor = new Method('__construct');

        if ($callback) {
            $callback($this->constructor);
        }

        return $this->constructor;
    }

    public function haveProperty(string $name): Reference
    {
        return $this->properties[] = new Reference($name);
    }

    public function getFactory(): ?callable
    {
        return $this->factory;
    }

    public function getConstructor(): ?Method
    {
        return $this->constructor;
    }

    /**
     * @return Reference[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
