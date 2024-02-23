<?php

declare(strict_types=1);

namespace blink\tests\di;

use blink\di\config\ConfigContainer;
use blink\di\Container;
use blink\di\exceptions\Exception;
use blink\di\exceptions\NotFoundException;
use blink\di\object\ObjectDefinition;
use blink\di\Reference;
use blink\tests\TestCase;
use blink\di\attributes\Inject;

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
                'expectException' => null,
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
        $this->assertEquals($expectResult, $result);
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

    public function testLoadDefinitionViaAttributes()
    {
        $obj = new class() {
            #[Inject('store.attr1')]
            public string    $attr1;

            #[Inject('store.attr2')]
            protected string $attr2;

            #[Inject('store.attr3')]
            private string   $attr3 = 'default';

            #[Inject]
            private DemoClassB $attr4;

            #[Inject('store.attr5', 'setAttr5')]
            private string   $attr5;
        };

        $container  = new Container();
        $definition = $container->loadDefinition(get_class($obj));

        $properties = $definition->getProperties();
        $this->assertCount(5, $properties);

        $this->assertReference($properties['attr1'], 'attr1', 'store.attr1', false, true, null, null);
        $this->assertReference($properties['attr2'], 'attr2', 'store.attr2', true, true, null, null);
        $this->assertReference($properties['attr3'], 'attr3', 'store.attr3', true, false, 'default', null);
        $this->assertReference($properties['attr4'], 'attr4', DemoClassB::class, true, true, null, null);
        $this->assertReference($properties['attr5'], 'attr5', 'store.attr5', true, true, null, 'setAttr5');
    }

    protected function assertReference(
        Reference $reference,
        string $name,
        string $referentName,
        bool $isGuarded,
        bool $isRequired,
        mixed $defaultValue,
        ?string $setter
    ) {
        $this->assertEquals($name, $reference->getName());
        $this->assertEquals($referentName, $reference->getReferentName());
        $this->assertEquals($isGuarded, $reference->isGuarded());
        $this->assertEquals($isRequired, $reference->isRequired());
        if (! $isRequired) {
            $this->assertEquals($defaultValue, $reference->getDefault());
        }
        $this->assertEquals($setter, $reference->getSetter());
    }

    public function testCreateConfigureObject()
    {
        $container  = new Container();

        $props = [
            'attr1' => 'value1',
            'attr2' => 'value2',
        ];

        $container->bind(ConfigureObject::class, $props);

        $object = $container->get(ConfigureObject::class);

        $this->assertEquals($props, $object->result);
    }
}
