<?php

declare(strict_types=1);

namespace blink\typing;

/**
 * Class Token
 *
 * @package blink\typing
 */
class Token
{
    const TEXT = 't';
    const OPEN_ANGLE = '<';
    const CLOSE_ANGLE = '>';
    const OPEN_PARENTHESES = '(';
    const CLOSE_PARENTHESES = ')';
    const UNION = '|';
    const COMMA = ',';

    const CONTROL_TOKENS = [
        self::OPEN_ANGLE,
        self::CLOSE_ANGLE,
        self::OPEN_PARENTHESES,
        self::CLOSE_PARENTHESES,
        self::UNION,
        self::COMMA,
    ];

    protected string  $type;
    protected ?string $value = null;

    public function __construct(string $type, ?string $value = null)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    public function expect(string $type): void
    {
        if ($type !== $this->type) {
            throw new SyntaxException(sprintf(
                'A %s token is expected, but %s is given',
                $this->getFormatedType($type),
                $this->getFormatedType($this->type),
            ));
        }
    }

    protected function getFormatedType(string $type): string
    {
        if ($type === self::TEXT) {
            return 'text';
        } else {
            return $type;
        }
    }

    public function isAny(array $types): bool
    {
        return in_array($this->type, $types, true);
    }

    public function is(string $type): bool
    {
        return $this->type === $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        $arr = [$this->type];

        if ($this->value !== null) {
            $arr[] = $this->value;
        }

        return $arr;
    }
}
