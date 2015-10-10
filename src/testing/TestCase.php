<?php

namespace blink\testing;

use PHPUnit_Framework_TestCase;

/**
 * Class TestCase
 *
 * @package blink\testing
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    use AuthTrait;

    protected $app;

    abstract public function createApplication();

    public function setUp()
    {
        if (!$this->app) {
            $this->app = $this->createApplication()->bootstrap();
        }
    }

    public function tearDown()
    {
        if ($this->app) {
            $this->app->shutdown();
            $this->app = null;
        }
    }
}
