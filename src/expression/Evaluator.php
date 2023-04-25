<?php

namespace blink\expression;

use blink\expression\expr\BinaryExpr;
use blink\expression\expr\Expr;
use blink\expression\expr\FuncExpr;
use blink\expression\expr\Literal;
use blink\expression\expr\Variable;

class Evaluator
{
    protected array $functions = [];

    public function __construct()
    {
        $this->registerBuiltinFunctions();
    }

    public function register(string $function, callable $handler): void
    {
        if (isset($this->functions[$function])) {
            throw new \Exception('Function already registered: ' . $function);
        }

        $this->functions[$function] = $handler;
    }

    public function registerBuiltinFunctions(): void
    {
        $this->register('if', fn($condition, $then, $else) => $condition ? $then : $else);
        $this->register('if_null', fn($condition, $else) => $condition ?? $else);
        $this->register('concat', fn(...$args) => implode('', $args));
    }

    public function evaluate(Expr $expr, array $variables = []): mixed
    {
        if ($expr instanceof Literal) {
            return $expr->value;
        } elseif ($expr instanceof Variable) {
            return $variables[$expr->name];
        } elseif ($expr instanceof FuncExpr) {
            $args = array_map(fn($arg) => $this->evaluate($arg, $variables), $expr->args);
            return ($this->functions[$expr->name])(...$args);
        } elseif ($expr instanceof BinaryExpr) {
            return $this->evaluateBinaryExpr($expr, $variables);
        } else {
            throw new \Exception('Unsupported expression type: ' . get_class($expr));
        }
    }

    protected function evaluateBinaryExpr(BinaryExpr $expr, array $variables): mixed
    {
        $left = $this->evaluate($expr->left, $variables);
        $right = $this->evaluate($expr->right, $variables);

        switch ($expr->op) {
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '/':
                return $left / $right;
            case '%':
                return $left % $right;
            case '==':
                return $left === $right;
            case '!=':
                return $left != $right;
            case '>':
                return $left > $right;
            case '>=':
                return $left >= $right;
            case '<':
                return $left < $right;
            case '<=':
                return $left <= $right;
            case 'xor':
                return $left xor $right;
            case 'in':
                return in_array($left, $right);
            case 'not in':
                return ! in_array($left, $right);
            case 'between':
                return $left >= $right[0] && $left <= $right[1];
            case 'not between':
                return $left < $right[0] || $left > $right[1];
            case 'contains':
                return str_contains($left, $right);
            case 'not contains':
                return ! str_contains($left, $right);
            case 'overlaps':
                return (bool) array_intersect($left, $right);
            case 'not overlaps':
                return ! array_intersect($left, $right);
            case 'starts with':
                return str_starts_with($left, $right);
            case 'ends with':
                return str_ends_with($left, $right);
            default:
                throw new \Exception('Unsupported operator: ' . $expr->op);
        }
    }
}
