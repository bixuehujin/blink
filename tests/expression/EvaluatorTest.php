<?php

namespace blink\tests\expression;

use blink\expression\Evaluator;
use blink\expression\expr\Expr;
use blink\tests\TestCase;
use function blink\expression\concat;
use function blink\expression\if_;
use function blink\expression\lit;
use function blink\expression\var_;

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
            ],
            [
                var_('a')->mod(var_('b')),
                1,
            ],
            [
                var_('a')->eq(var_('b')),
                false,
            ],
            [
                var_('a')->neq(var_('b')),
                true,
            ],
            [
                var_('a')->gt(var_('b')),
                false,
            ],
            [
                var_('a')->gte(var_('b')),
                false,
            ],
            [
                var_('a')->lt(var_('b')),
                true,
            ],
            [
                var_('a')->lte(var_('b')),
                true,
            ],
            [
                lit('foo')->in(lit(['foo'])),
                true,
            ],
            [
                lit('foo')->notIn(lit(['foo'])),
                false,
            ],
            [
                lit('ifoobar')->contains(lit('foo')),
                true,
            ],
            [
                lit('ifoobar')->notContains(lit('foo')),
                false,
            ],
            [
                lit('ifoobar')->startsWith(lit('foo')),
                false,
            ],
            [
                lit('ifoobar')->endsWith(lit('bar')),
                true,
            ],
            [
                lit([1, 2])->overlaps(lit([2, 3])),
                true,
            ],
            [
                lit([1, 2])->notOverlaps(lit([2, 3])),
                false,
            ],
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
