<?php


namespace blink\tests\di;


class DemoClassA
{
    /**
     * @injector(name=mysql.host)
     */
    public string $host;
    /**
     * @injector(name=mysql.port)
     */
    protected int $port;
    /**
     * DemoClassA constructor.
     *
     * @param DemoClassB $a
     * @param $value
     */
    public function __construct(DemoClassB $a, $value = 1)
    {
    }
}
