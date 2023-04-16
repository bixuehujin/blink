<?php

namespace blink\expression\expr;


class BinaryExpr extends Expr
{
    const OP_EQ = '=';
    const OP_NEQ = '!=';
    const OP_GT = '>';
    const OP_EGT = '>=';
    const OP_LT = '<';
    const OP_ELT = '<=';
    const OP_LIKE = 'like';
    const OP_NOT_LIKE = 'not like';
    const OP_IN = 'in';
    const OP_NOT_IN = 'not in';
    const OP_BETWEEN = 'between';
    const OP_PLUS = '+';
    const OP_MINUS = '-';
    const OP_MULTIPLY = '*';
    const OP_DIVIDE = '/';
    const OP_MOD = '%';
    const OP_XOR = 'xor';

    const OPS = [
        self::OP_EQ,
        self::OP_NEQ,
        self::OP_GT,
        self::OP_EGT,
        self::OP_LT,
        self::OP_ELT,
        self::OP_LIKE,
        self::OP_NOT_LIKE,
        self::OP_IN,
        self::OP_NOT_IN,
        self::OP_BETWEEN,
        self::OP_PLUS,
        self::OP_MINUS,
        self::OP_MULTIPLY,
        self::OP_DIVIDE,
        self::OP_MOD,
        self::OP_XOR,
    ];

    public mixed $left;
    public string $op;
    public mixed $right;

    public function __construct(mixed $left, string $op, mixed $right)
    {
        if (! in_array($op, self::OPS, true)) {
            throw new \InvalidArgumentException("Invalid operator: $op");
        }

        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
    }

    public static function type(): string
    {
        return 'binary';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'left' => $this->left,
            'op' => $this->op,
            'right' => $this->right,
        ];
    }
}
