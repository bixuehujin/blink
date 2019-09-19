<?php

namespace blink\testing;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * @package blink\testing
 */
abstract class TestCase extends BaseTestCase
{
    protected $app;

    abstract public function createApplication();

    public function setUp()
    {
        if (!$this->app) {
            $this->app = $this->createApplication()->bootstrapIfNeeded();
        }
    }

    /**
     * Returns a new request actor for testing.
     *
     * @return RequestActor
     * @since 0.3.0
     */
    public function actor()
    {
        return new RequestActor($this, $this->createApplication()->bootstrapIfNeeded());
    }

    public function tearDown()
    {
        if ($this->app) {
            $this->app->shutdown();
            $this->app = null;
        }
    }
}
