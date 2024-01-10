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

    public function setUp(): void
    {
        if (!$this->app) {
//            $this->app = $this->createApplication()->bootstrapIfNeeded();
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
        return new RequestActor($this, $this->createApplication());
    }

    public function tearDown(): void
    {
        if ($this->app) {
            $this->app = null;
        }
    }
}
