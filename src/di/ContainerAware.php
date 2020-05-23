<?php

namespace blink\di;

/**
 * Interface ContainerAware
 *
 * @package blink\di
 */
interface ContainerAware
{
    public function setContainer(Container $container);
    public function getContainer(): Container;
}
