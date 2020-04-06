<?php

declare(strict_types=1);

namespace blink\tests\injector;

use blink\injector\CompositeStore;
use blink\injector\config\ConfigContainer;
use blink\injector\Container;
use blink\injector\exceptions\Exception;
use blink\injector\exceptions\NotFoundException;
use blink\injector\object\ObjectCreator;
use blink\injector\object\ObjectDefinition;
use blink\tests\TestCase;

/**
 * Class InjectorTest
 *
 * @package blink\tests\injector
 */
class InjectorTest extends TestCase
{
    protected function createConfigContainer(): ConfigContainer
    {
        $container = new ConfigContainer();

        $container->define('mysql.host')->default('localhost');
        $container->define('mysql.password')->required();
        $container->define('mysql.port')->required();
        $container->apply([
            'mysql.port' => 3306,
        ]);
        return $container;
    }

    public function configInjectCases()
    {
        return [
            [
                'name'            => 'unknown',
                'expectException' => NotFoundException::class,
                'expectResult'    => null,
            ],
            [
                'name'            => 'mysql.password',
                'expectException' => Exception::class,
                'expectResult'    => null,
            ],
            [
                'name'            => 'mysql.host',
                'expectException' => null,
                'expectResult'    => 'localhost',
            ],
            [
                'name'            => 'mysql.port',
                'expectException' => null,
                'expectResult'    => 3306,
            ],
        ];
    }

    /**
     * @dataProvider configInjectCases
     */
    public function testConfigInject(string $id, ?string $expectException, $expectResult = null)
    {
        $injector = $this->createConfigContainer();

        if ($expectException) {
            $this->expectException($expectException);
        }

        $result = $injector->get($id);
        if ($expectResult) {
            $this->assertEquals($expectResult, $result);
        }
    }

    protected function createObjectInjector()
    {
        $container = new Container([
            $this->createConfigContainer(),
        ]);

        $container->extend(DemoClassA::class, function (ObjectDefinition $definition) {
            $definition->haveProperty('host')->referenceTo('mysql.host');
            $definition->haveProperty('port')->referenceTo('mysql.port')->guarded();
        });

        return $container;
    }

    public function objectInjectCases()
    {
        return [
            [
                'name'            => DemoClassA::class,
                'expectException' => null,
                'expectResult'    => DemoClassA::class,
            ],
        ];
    }

    /**
     * @dataProvider objectInjectCases
     */
    public function testObjectInject(string $id, ?string $expectException, $expectResult = null)
    {
        $injector = $this->createObjectInjector();

        if ($expectException) {
            $this->expectException($expectException);
        }

        $result = $injector->make($id);
        if ($expectResult) {
            $this->assertInstanceOf($expectResult, $result);
        }
    }
}
