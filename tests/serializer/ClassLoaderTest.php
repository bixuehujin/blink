<?php

declare(strict_types=1);

namespace blink\tests\serializer;

use blink\serializer\attributes\Property;
use blink\serializer\ClassTypeLoader;
use blink\tests\serializer\stubs\StubClass1;
use blink\tests\serializer\stubs\StubClass2;
use blink\tests\TestCase;
use blink\typing\Manager;
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
        $typing = new Manager([], [new ClassTypeLoader()]);

        return [
            // 1. public fields
            [
                StubClass1::class,
                $typing->structOf(
                    StubClass1::class,
                    [
                        new StructField('a', $typing->parse('integer'), [
                            'property' => new Property( ),
                        ]),
                        new StructField('b', $typing->parse('string|integer'), [
                            'property' => new Property( ),
                        ]),
                        new StructField('c', $typing->parse('string|null'), [
                            'property' => new Property( ),
                        ]),
                    ],
                ),
            ],

            // 2. private fields with setter and getters
            [
                StubClass2::class,
                $typing->structOf(
                    StubClass2::class,
                    [
                        new StructField('a', $typing->parse('integer'), [
                            'property' => new Property( true),
                        ]),
                        new StructField('b', $typing->parse('integer'), [
                            'property' => new Property(true, '', 'setB'),
                        ]),
                        new StructField('c', $typing->parse('integer'), [
                            'property' => new Property(true, 'getC', ''),
                        ]),
                        new StructField('d', $typing->parse('integer'), [
                            'property' => (new Property())->withDefaultValue(1),
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
        $typing = new Manager([], [new ClassTypeLoader()]);
        $type   = $typing->parse($class);
        $this->assertEquals($expected, $type);
    }
}
