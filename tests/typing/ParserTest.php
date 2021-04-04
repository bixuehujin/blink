<?php

declare(strict_types=1);

namespace blink\tests\typing;

use blink\tests\TestCase;
use blink\typing\Registry;
use blink\typing\SyntaxException;
use blink\typing\Token;
use blink\typing\Tokenizer;
use blink\typing\types\FloatType;
use blink\typing\types\IntegerType;
use blink\typing\types\NullType;
use blink\typing\types\StringType;
use blink\typing\types\UnionType;

/**
 * Class ParserTest
 *
 * @package blink\tests\typing
 */
class ParserTest extends TestCase
{
    public function typeTokenizeCases(): array
    {
        return [
            // 1. sample type
            [
                'list',
                [
                    [Token::TEXT, 'list'],
                ],
            ],
            // 2. list type
            [
                'list<string>',
                [
                    [Token::TEXT, 'list'],
                    [Token::OPEN_ANGLE],
                    [Token::TEXT, 'string'],
                    [Token::CLOSE_ANGLE],
                ],
            ],
            // 3. map type
            [
                'map<string, string>',
                [
                    [Token::TEXT, 'map'],
                    [Token::OPEN_ANGLE],
                    [Token::TEXT, 'string'],
                    [Token::COMMA],
                    [Token::TEXT, 'string'],
                    [Token::CLOSE_ANGLE],
                ],
            ],
            // 4. tuple type
            [
                '(string, integer)',
                [
                    [Token::OPEN_PARENTHESES],
                    [Token::TEXT, 'string'],
                    [Token::COMMA],
                    [Token::TEXT, 'integer'],
                    [Token::CLOSE_PARENTHESES],
                ],
            ],
            // 5. spaces before comma
            [
                '(string , integer)',
                [
                    [Token::OPEN_PARENTHESES],
                    [Token::TEXT, 'string'],
                    [Token::COMMA],
                    [Token::TEXT, 'integer'],
                    [Token::CLOSE_PARENTHESES],
                ],
            ],
            // 6. union
            [
                'string|null',
                [
                    [Token::TEXT, 'string'],
                    [Token::UNION],
                    [Token::TEXT, 'null'],
                ],
            ],
        ];
    }

    /**
     * @param string $definition
     * @param array $expected
     * @dataProvider typeTokenizeCases
     */
    public function testTokenize(string $definition, array $expected): void
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($definition);
        $tokens = array_map(fn (Token $token) => $token->toArray(), $tokens);
        $this->assertEquals($expected, $tokens);
    }
    

    public function parseCases(): array
    {
        $manager = new Registry();
        
        return [
            [
                '',
                'The type definition is empty',
            ],
            [
                'integer',
                new IntegerType(),
            ],
            [
                '<',
                'A text token is expected, but < is given',
            ],
            [
                'integer>',
                'Unexpected token >',
            ],
            [
                'integer)',
                'Unexpected token )',
            ],
            [
                'integer,',
                'Unexpected token ,',
            ],
            [
                'integer|null',
                new UnionType(new IntegerType(), new NullType())
            ],
            [
                'integer|null|float',
                new UnionType(new IntegerType(), new NullType(), new FloatType())
            ],
            [
                'list<string',
                'The token < is not properly closed',
            ],
            [
                'list<string>',
                $manager->genericOf('list', [
                    new StringType(),
                ]),
            ],
            [
                'map<string, float>',
                $manager->genericOf('map', [
                    new StringType(),
                    new FloatType(),
                ]),
            ],
            [
                'map<string, float|null>',
                $manager->genericOf('map', [
                    new StringType(),
                    new UnionType(
                        new FloatType(),
                        new NullType(),
                    ),
                ]),
            ],
            [
                'map<string, float>|null',
                new UnionType(
                    $manager->genericOf('map', [
                        new StringType(),
                        new FloatType(),
                    ]),
                    new NullType(),
                ),
            ],
            [
                'null|map<string, float>',
                new UnionType(
                    new NullType(),
                    $manager->genericOf('map', [
                        new StringType(),
                        new FloatType(),
                    ]),
                ),
            ],
            [
                'null|map<string, list<float>>',
                new UnionType(
                    new NullType(),
                    $manager->genericOf('map', [
                        new StringType(),
                        $manager->genericOf('list', [
                            new FloatType(),
                        ])
                    ]),
                ),
            ],
        ];
    }

    /**
     * @dataProvider parseCases
     */
    public function testParse(string $definition, mixed $expected): void
    {
        $manager = new Registry();

        try {
            $type = $manager->parse($definition);
            $this->assertEquals($expected, $type);
        } catch (SyntaxException $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
    }
}
