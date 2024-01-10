<?php

namespace blink\expression\expr;

class BinaryExpr extends Expr
{
    const OP_EQ = '==';
    const OP_NEQ = '!=';
    const OP_GT = '>';
    const OP_GTE = '>=';
    const OP_LT = '<';
    const OP_LTE = '<=';
    const OP_PLUS = '+';
    const OP_MINUS = '-';
    const OP_MULTIPLY = '*';
    const OP_DIVIDE = '/';
    const OP_MOD = '%';
    const OP_XOR = 'xor';
    const OP_CONTAINS = 'contains';
    const OP_NOT_CONTAINS = 'not contains';
    const OP_IN = 'in';
    const OP_NOT_IN = 'not in';
    const OP_BETWEEN = 'between';
    const OP_OVERLAPS = 'overlaps';
    const OP_NOT_OVERLAPS = 'not overlaps';
    const OP_STARTS_WITH = 'starts with';
    const OP_ENDS_WITH = 'ends with';

    const OPS = [
        self::OP_EQ,
        self::OP_NEQ,
        self::OP_GT,
        self::OP_GTE,
        self::OP_LT,
        self::OP_LTE,
        self::OP_PLUS,
        self::OP_MINUS,
        self::OP_MULTIPLY,
        self::OP_DIVIDE,
        self::OP_MOD,
        self::OP_XOR,
        self::OP_BETWEEN,
        self::OP_CONTAINS,
        self::OP_NOT_CONTAINS,
        self::OP_IN,
        self::OP_NOT_IN,
        self::OP_OVERLAPS,
        self::OP_NOT_OVERLAPS,
        self::OP_STARTS_WITH,
        self::OP_ENDS_WITH,
    ];

    public Expr $left;
    public string $op;
    public Expr $right;

    public function __construct(mixed $left, string $op, mixed $right)
    {
        $this->left = static::normalize($left);
        $this->op = $op;
        $this->right = static::normalize($right);
    }

    public static function type(): string
    {
        return 'binary';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'left' => $this->left->toArray(),
            'op' => $this->op,
            'right' => $this->right->toArray(),
        ];
    }
}
