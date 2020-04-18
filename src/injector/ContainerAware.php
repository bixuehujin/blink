<?php

namespace blink\injector;

/**
 * Interface ContainerAware
 *
 * @package blink\injector
 */
interface ContainerAware
{
    public function setContainer(Container $container);
    public function getContainer(): Container;
}
