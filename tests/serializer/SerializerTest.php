<?php

declare(strict_types=1);

namespace blink\tests\serializer;

use blink\serializer\attributes\ComputedProperty;
use blink\serializer\ClassTypeLoader;
use blink\serializer\normalizer\DateTimeNormalizer;
use blink\serializer\normalizer\ListNormalizer;
use blink\serializer\normalizer\ObjectNormalizer;
use blink\serializer\normalizer\ScalarNormalizer;
use blink\serializer\normalizer\UnionNormalizer;
use blink\serializer\Serializer;
use blink\tests\TestCase;
use blink\typing\Registry;
use blink\serializer\attributes\Property;

class DemoData
{
    public int $a = 0;
    public int|string $b = 1;

    #[Property(getter: true)]
    public string $c = '';

    public function getC(): string
    {
        return 'c';
    }

    #[ComputedProperty('d')]
    public function getD(): string
    {
        return 'd';
    }
}

/**
 * Class SerializerTest
 *
 * @package blink\tests\serializer
 */
class SerializerTest extends TestCase
{
    public function serializeCases()
    {
        return [
            // 1. basic types
            [
                1,
                'integer',
                1,
            ],
            [
                1.2,
                'float',
                1.2,
            ],
            [
                null,
                'null',
                'null',
            ],
            [
                'foo',
                'string',
                '"foo"',
            ],

            // 2. array of basic types
            [
                [1],
                'list<integer>',
                '[1]'
            ],

            // datetime
            [
                new \DateTime('2020-10-01T00:00:00+08:00'),
                \DateTime::class,
                '"2020-10-01T00:00:00+08:00"',
            ],

            [
                new DemoData(),
                DemoData::class,
                '{"a":0,"b":"1","c":"c","d":"d"}',
            ]
        ];
    }

    /**
     * @dataProvider serializeCases
     */
    public function testNormalize($input, $typeDef, $expected): void
    {
        $serializer = $this->buildSerializer();

        $result = $serializer->serialize($input, $typeDef);
        $this->assertEquals($expected, $result);
    }

    protected function buildSerializer(): Serializer
    {
        $typing = new Registry([], [new ClassTypeLoader()]);

        $serializer = new Serializer($typing, [
            new ScalarNormalizer(),
            new ListNormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(),
            new UnionNormalizer(),
        ]);

        return $serializer;
    }

    public function denormalizeCases(): array
    {
        return [

        ];
    }

    /**
     * @dataProvider denormalizeCases
     */
    public function testDenormalize(mixed $input, string $typeDef, mixed $expected): void
    {
    }
}
