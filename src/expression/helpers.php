<?php

namespace blink\expression;

use blink\expression\expr\AggExpr;
use blink\expression\expr\AndExpr;
use blink\expression\expr\BinaryExpr;
use blink\expression\expr\Column;
use blink\expression\expr\Expr;
use blink\expression\expr\FuncExpr;
use blink\expression\expr\Literal;
use blink\expression\expr\OrExpr;
use blink\expression\expr\Relation;
use blink\expression\expr\Variable;


function col(string $column): Column
{
    return new Column($column);
}

function rel(string $name): Relation
{
    return new Relation($name);
}

function lit(mixed $value): Literal
{
    return new Literal($value);
}

function var_(string $name): Variable
{
    return new Variable($name);
}

function and_(Expr ...$exprs): AndExpr
{
    return new AndExpr(...$exprs);
}

function or_(Expr ...$exprs): OrExpr
{
    return new OrExpr(...$exprs);
}

function binary(Expr $left, string $operator, Expr $right): BinaryExpr
{
    return new BinaryExpr($left, $operator, $right);
}

function if_(Expr $condition, Expr $then, Expr $else): FuncExpr
{
    return new FuncExpr('if', [$condition, $then, $else]);
}

function if_null(Expr $condition, Expr $else): FuncExpr
{
    return new FuncExpr('if_null', [$condition, $else]);
}

function concat(mixed ...$args): FuncExpr
{
    return new FuncExpr('concat', $args);
}

function now(): FuncExpr
{
    return new FuncExpr('now', []);
}

function count(string|Expr $expr, bool $distinct = false): AggExpr
{
    return new AggExpr('count', $expr, ['distinct' => $distinct]);
}

function sum(string|Expr $expr): AggExpr
{
    return new AggExpr('sum', $expr);
}

function max(string|Expr $expr): AggExpr
{
    return new AggExpr('max', $expr);
}

function min(string|Expr $expr): AggExpr
{
    return new AggExpr('min', $expr);
}

function avg(string|Expr $expr): AggExpr
{
    return new AggExpr('avg', $expr);
}

function group_agg(string|Expr $expr): AggExpr
{
    return new AggExpr('group_agg', $expr);
}
