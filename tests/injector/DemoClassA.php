<?php


namespace blink\tests\injector;


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
        var_dump($a, $value);
    }
}
