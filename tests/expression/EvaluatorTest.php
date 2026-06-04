<?php

namespace blink\tests\expression;

use blink\expression\Evaluator;
use blink\expression\expr\Expr;
use blink\expression\expr\AggExpr;
use blink\expression\expr\AndExpr;
use blink\expression\expr\BinaryExpr;
use blink\expression\expr\Column;
use blink\expression\expr\FuncExpr;
use blink\expression\expr\HasExpr;
use blink\expression\expr\Literal;
use blink\expression\expr\OrExpr;
use blink\expression\expr\Relation;
use blink\expression\expr\Variable;
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

    public function fromArrayCases(): array
    {
        return [
            'literal' => [
                (new Literal('hello'))->as('a')->disabled(true),
            ],
            'literal_int' => [
                new Literal(42),
            ],
            'variable' => [
                new Variable('myvar'),
            ],
            'column' => [
                new Column('mycol'),
            ],
            'binary' => [
                new BinaryExpr(new Column('age'), '>', new Literal(18)),
            ],
            'and' => [
                new AndExpr(new Column('a'), new Column('b')),
            ],
            'and_single' => [
                new AndExpr(new Column('x')),
            ],
            'or' => [
                new OrExpr(new Column('a'), new Column('b')),
            ],
            'func' => [
                new FuncExpr('if', [new Literal(true), new Literal(1), new Literal(2)]),
            ],
            'func_concat' => [
                new FuncExpr('concat', [new Literal('a'), new Literal('b'), new Literal('c')]),
            ],
            'agg' => [
                new AggExpr('count', new Column('id'), ['distinct' => true]),
            ],
            'agg_no_options' => [
                new AggExpr('sum', new Column('amount')),
            ],
            'relation' => [
                new Relation('users', [new Column('id'), new Column('name')], (new Column('active'))->eq(new Literal(true))),
            ],
            'relation_no_filter' => [
                new Relation('items', [new Column('id')], null),
            ],
            'has' => [
                new HasExpr('posts', (new Column('published'))->eq(new Literal(true))),
            ],
        ];
    }

    /**
     * @dataProvider fromArrayCases
     */
    public function testFromArrayRoundtrip(Expr $original): void
    {
        $restored = Expr::fromArray($original->toArray());

        $this->assertInstanceOf($original::class, $restored);
        $this->assertEquals($original->toArray(), $restored->toArray());
    }

    public function testFromArrayDeepNesting(): void
    {
        $expr = new AndExpr(
            (new Column('age'))->gt(new Literal(18)),
            new OrExpr(
                (new Column('role'))->in(new Literal(['admin', 'moderator'])),
                new HasExpr('permissions', (new Column('level'))->gte(new Literal(5)))
            )
        );

        $restored = Expr::fromArray($expr->toArray());

        $this->assertInstanceOf(AndExpr::class, $restored);
        $this->assertCount(2, $restored->exprs);
        $this->assertInstanceOf(BinaryExpr::class, reset($restored->exprs));
        $this->assertInstanceOf(OrExpr::class, $restored->exprs[1]);
        $this->assertEquals($expr->toArray(), $restored->toArray());
    }

    public function testFromArrayPreservesAliasAndDisabled(): void
    {
        $expr = (new Column('name'))->as('user_name')->disabled(true);
        $restored = Expr::fromArray($expr->toArray());

        $this->assertSame('user_name', $restored->alias);
        $this->assertTrue($restored->disabled);
    }

    public function testFromArrayDefaultAliasAndDisabled(): void
    {
        $expr = new Literal(42);
        $restored = Expr::fromArray($expr->toArray());

        $this->assertNull($restored->alias);
        $this->assertFalse($restored->disabled);
    }

    public function testFromArrayUnknownTypeThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown expression type');

        Expr::fromArray(['type' => 'unknown_type']);
    }

    public function testFromArrayMissingTypeThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown expression type');

        Expr::fromArray(['value' => 42]);
    }

    public function testRegisterType(): void
    {
        Expr::registerType('__test_custom__', Literal::class);

        $data = ['type' => '__test_custom__', 'value' => 'custom_value'];
        $restored = Expr::fromArray($data);

        $this->assertInstanceOf(Literal::class, $restored);
        $this->assertSame('custom_value', $restored->value);
    }

    public function testRegisterTypeConflictThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already registered');

        Expr::registerType('literal', Variable::class);
    }

    public function testRegisterTypeSameClassSucceeds(): void
    {
        // 相同 type+class 不应抛异常
        Expr::registerType('literal', Literal::class);
        $this->assertTrue(true);
    }

    public function testRegisterTypeNonExprClassThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must extend');

        Expr::registerType('__test_bad__', \stdClass::class);
    }

    public function testReplaceType(): void
    {
        // registerType 对新 type
        Expr::registerType('__test_replace__', Literal::class);

        // replaceType 替换为另一个子类
        Expr::replaceType('__test_replace__', Variable::class);

        $restored = Expr::fromArray(['type' => '__test_replace__', 'name' => 'replaced']);

        $this->assertInstanceOf(Variable::class, $restored);
        $this->assertSame('replaced', $restored->name);
    }

    public function testReplaceTypeOverridesBuiltin(): void
    {
        $original = Expr::fromArray(['type' => 'literal', 'value' => 42]);
        $this->assertInstanceOf(Literal::class, $original);

        // 替换内置 'literal' type
        Expr::replaceType('literal', Variable::class);

        $restored = Expr::fromArray(['type' => 'literal', 'name' => 'hijacked']);
        $this->assertInstanceOf(Variable::class, $restored);
        $this->assertSame('hijacked', $restored->name);

        // 恢复
        Expr::replaceType('literal', Literal::class);
    }

    public function testReplaceTypeNonExprClassThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must extend');

        Expr::replaceType('literal', \stdClass::class);
    }
}
