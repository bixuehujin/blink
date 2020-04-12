<?php

declare(strict_types=1);

namespace blink\kernel;

use blink\injector\config\ConfigContainer;
use blink\injector\config\ConfigDefinition;
use blink\injector\Container;

abstract class Kernel
{
    protected Container       $container;
    protected ConfigContainer $configContainer;
    /**
     * @var ServiceProvider[]
     */
    protected array $providers = [];

    public function __construct()
    {
        $this->configContainer = new ConfigContainer();
        $this->container       = new Container([$this->configContainer]);
    }

    public function define(string $name): ConfigDefinition
    {
        return $this->configContainer->define($name);
    }

    public function set(string $name, $value)
    {
        $this->configContainer->apply([$name => $value]);
    }

    public function add(ServiceProvider $provider)
    {
        $this->providers[] = $provider;
    }

    public function bootstrap()
    {
        foreach ($this->providers as $provider) {
            $provider->register($this);
        }
    }
}
