<?php

namespace blink\injector\object;

use blink\injector\Reference;


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

    /**
     * @return Reference[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
