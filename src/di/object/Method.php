<?php

namespace blink\di\object;

use blink\di\Reference;

class Method
{
    protected string $methodName;
    /**
     * @var Reference[]
     */
    protected array $arguments = [];

    public function __construct(string $methodName, array $arguments = [])
    {
        $this->methodName = $methodName;
        $this->arguments  = $arguments;
    }

    public function haveArgument(string $name): Reference
    {
        return $this->arguments[$name] = new Reference($name);
    }

    public function getArgument(string $name): Reference
    {
        $argument = $this->arguments[$name] ?? null;

        if (! $argument) {
            throw new \InvalidArgumentException("Argument $name not found in method $this->methodName.");
        }

        return $argument;
    }

    /**
     * @return Reference[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
