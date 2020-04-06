<?php

declare(strict_types=1);

namespace blink\tests\injector;

use blink\injector\CompositeStore;
use blink\injector\config\ConfigStore;
use blink\injector\Injector;
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
    protected function createConfigStore(): ConfigStore
    {
        $configStore = new ConfigStore();

        $configStore->define('mysql.host')->default('localhost');
        $configStore->define('mysql.password')->required();
        $configStore->define('mysql.port')->required();
        $configStore->appendConfigMap([
            'mysql.port' => 3306,
        ]);
        return $configStore;
    }

    protected function createConfigInjector()
    {
        return new Injector($this->createConfigStore());
    }

    public function configInjectCases()
    {
        return [
            [
                'name'            => 'unknown',
                'expectException' => \Exception::class,
                'expectResult'    => null,
            ],
            [
                'name'            => 'mysql.password',
                'expectException' => \Exception::class,
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
        $injector = $this->createConfigInjector();

        if ($expectException) {
            $this->expectException($expectException);
        }

        $result = $injector->make($id);
        if ($expectResult) {
            $this->assertEquals($expectResult, $result);
        }
    }

    protected function createObjectInjector()
    {
        $creator = new ObjectCreator();

        $creator->extend(DemoClassA::class, function (ObjectDefinition $definition) {
            $definition->haveProperty('host')->referenceTo('mysql.host');
            $definition->haveProperty('port')->referenceTo('mysql.port')->guarded();
        });

        return new Injector(new CompositeStore($creator, $this->createConfigStore()));
    }

    public function objectInjectCases()
    {
        return [
            [
                'name'            => DemoClassA::class,
                'expectException' => null,
                'expectResult'    => null,
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
            $this->assertEquals($expectResult, $result);
        }
    }
}
