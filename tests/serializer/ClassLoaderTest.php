<?php

declare(strict_types=1);

namespace blink\tests\serializer;

use blink\serializer\attributes\Property;
use blink\serializer\ClassTypeLoader;
use blink\tests\serializer\stubs\StubClass1;
use blink\tests\serializer\stubs\StubClass2;
use blink\tests\TestCase;
use blink\typing\Registry;
use blink\typing\types\StructField;

/**
 * Class ClassLoaderTest
 *
 * @package blink\tests\serializer
 */
class ClassLoaderTest extends TestCase
{
    public function classCases(): array
    {
        $typing = new Registry([], [new ClassTypeLoader()]);

        return [
            // 1. Public fields
            [
                StubClass1::class,
                $typing->structOf(
                    StubClass1::class,
                    [
                        new StructField('a', $typing->parse('integer'), [
                            'property' => (new Property())->withName('a')->withGuarded(false),
                        ]),
                        new StructField('b', $typing->parse('string|integer'), [
                            'property' => (new Property())->withName('b')->withGuarded(false),
                        ]),
                        new StructField('c', $typing->parse('string|null'), [
                            'property' => (new Property())->withName('c')->withGuarded(false),
                        ]),
                    ],
                ),
            ],

            // 2. Private fields with setter and getters
            [
                StubClass2::class,
                $typing->structOf(
                    StubClass2::class,
                    [
                        new StructField('a', $typing->parse('integer'), [
                            'property' => (new Property())->withName('a')->withGuarded(true),
                        ]),
                        new StructField('b', $typing->parse('integer'), [
                            'property' => (new Property('', 'setB'))->withName('b')->withGuarded(true),
                        ]),
                        new StructField('c', $typing->parse('integer'), [
                            'property' => (new Property('getC', ''))->withName('c')->withGuarded(true),
                        ]),
                        new StructField('d', $typing->parse('integer'), [
                            'property' => (new Property())->withDefaultValue(1)->withName('d')->withGuarded(true),
                        ]),
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider classCases
     */
    public function testLoadClassDefinitions(string $class, $expected): void
    {
        $typing = new Registry([], [new ClassTypeLoader()]);
        $type   = $typing->parse($class);
        $this->assertEquals($expected, $type);
    }
}
