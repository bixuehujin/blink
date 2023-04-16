<?php

namespace blink\tests\expression;

use blink\expression\Evaluator;
use blink\expression\expr\Expr;
use blink\tests\TestCase;
use function blink\expression\expr\concat;
use function blink\expression\expr\if_;
use function blink\expression\expr\lit;
use function blink\expression\expr\var_;

class EvaluatorTest extends TestCase
{
    public function mathExpressionCases(): array
    {
        return [
            [
                lit(1)
                    ->plus(lit(2))
                    ->minus(lit(1))
                    ->multiply(lit(2))
                    ->divide(lit(2)),
                2,
            ],
            [
                var_('a')->plus(var_('b')),
                3,
            ]
        ];
    }

    /**
     * @param Expr $expr
     * @param mixed $result
     * @dataProvider mathExpressionCases
     */
    public function testEvaluateMathExpressions(Expr $expr, mixed $result): void
    {
        $evaluator = new Evaluator();

        $variables = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        $this->assertEquals($result, $evaluator->evaluate($expr, $variables));
    }


    public function functionCases(): array
    {
        return [
            [
                if_(lit(true), lit(1), lit(2)),
                1,
            ],
            [
                if_(lit(false), lit(1), lit(2)),
                2,
            ],
            [
                concat(lit(1), lit(','), lit(2)),
                '1,2',
            ],
            [
                concat(var_('a'), var_('b')),
                'foobar',
            ],
        ];
    }

    /**
     * @param Expr $expr
     * @param mixed $result
     * @return void
     * @dataProvider functionCases
     */
    public function testEvaluateFunctions(Expr $expr, mixed $result): void
    {
        $evaluator = new Evaluator();

        $this->assertEquals($result, $evaluator->evaluate($expr, ['a' => 'foo', 'b' => 'bar']));
    }
}
