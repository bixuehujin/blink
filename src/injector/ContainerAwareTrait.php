<?php

namespace blink\injector;


trait ContainerAwareTrait
{
    private Container $container;

    public function setContainer(Container $container)
    {
        return $this->container = $container;
    }
    public function getContainer(): Container
    {
        return $this->container;
    }
}
