<?php

declare(strict_types=1);

namespace blink\typing;

use blink\core\NotSupportedException;
use blink\typing\types\UnionType;

/**
 * Class Parser
 *
 * @package blink\typing
 */
class Parser
{
    protected Manager $manager;
    protected Tokenizer $tokenizer;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->tokenizer = new Tokenizer();
    }

    /**
     * @param Token[] $tokens
     * @param int $pos
     * @return Type
     */
    protected function parseScalarType(array $tokens, int $pos): Type
    {
        $token = $tokens[$pos];

        $token->expect(Token::TEXT);

        return $this->manager->getType($token->getValue());
    }

    public function parse(string $definition): Type
    {
        $tokens = $this->tokenizer->tokenize($definition);

        if (empty($tokens)) {
            throw new SyntaxException('The type definition is empty');
        }

        return $this->parseInternal($tokens);
    }

    /**
     * @param Token[] $tokens
     * @param int $pos
     * @return Token[]
     */
    protected function findSubTokens(array $tokens, array $stopTypes, int $pos): array
    {
        $angles = $parentheses = [];

        for ($endPos = $pos; ; $endPos++) {
            $token = $tokens[$endPos] ?? null;
            if (!$token) {
                break;
            }

            if ($token->is(Token::OPEN_ANGLE)) {
                $angles[] = $endPos;
            } elseif ($token->is(Token::OPEN_PARENTHESES)) {
                $parentheses[] = $endPos;
            } elseif ($token->is(Token::CLOSE_ANGLE)) {
                array_pop($angles);
            } elseif ($token->is(Token::CLOSE_PARENTHESES)) {
                array_pop($parentheses);
            }

            if ($token->isAny($stopTypes) && empty($angles) && empty($parentheses)) {
                break;
            }
        }

        if (!empty($angles)) {
            throw new SyntaxException('The token < is not properly closed');
        }

        if (!empty($parentheses)) {
            throw new SyntaxException('The token ( is not properly closed');
        }

        return array_slice($tokens, $pos, $endPos - $pos);
    }

    /**
     * @param Token[] $tokens
     * @param int $pos
     * @return Type
     * @throws SyntaxException
     */
    public function parseInternal(array $tokens): Type
    {
        $pos = 0;

        if (count($tokens) === 1) {
            return $this->parseScalarType($tokens, $pos);
        }

        if ($tokens[$pos]->is(Token::TEXT)) {
            return $this->parseSimpleType($tokens);
        } elseif ($tokens[0]->is(Token::OPEN_PARENTHESES)) {
            return $this->parseTupleType($tokens);
        } else {
            throw new SyntaxException('Unexpected token ' . $tokens[0]->type());
        }
    }

    /**
     * @param Token[] $tokens
     * @return Type
     * @throws SyntaxException
     */
    protected function parseSimpleType(array $tokens): Type
    {
        $pos    = 0;
        $endPos = count($tokens) - 1;

        while ($endPos > $pos) {
            $token = $tokens[$pos];
            $next  = $tokens[$pos + 1];
            if ($next->is(Token::OPEN_ANGLE)) {
                $token->expect(Token::TEXT);
                $subTokens = $this->findSubTokens($tokens, [Token::CLOSE_ANGLE], $pos + 1);
                $parameterTypes = $this->parseGenericParameterTypes(array_slice($subTokens, 1));
                $subType        = $this->manager->genericOf($token->getValue(), $parameterTypes);
                if (!isset($type)) {
                    $type = $subType;
                } elseif ($type instanceof UnionType) {
                    $type->appendType($subType);
                } else {
                    $type = new UnionType(
                        $type,
                        $subType,
                    );
                }
                $pos += count($subTokens) + 1;
            } elseif ($next->is(Token::UNION)) {
                $subTokens = $this->findSubTokens($tokens, [Token::UNION], $pos + 2);
                $subType   = $this->parseInternal($subTokens);

                if (!isset($type)) {
                    $type = $this->manager->unionOf(
                        $this->parseScalarType($tokens, $pos),
                        $subType,
                    );
                } elseif ($type instanceof UnionType) {
                    $type->appendType($subType);
                } else {
                    $type = $this->manager->unionOf(
                        $type,
                        $subType,
                    );
                }

                $pos += count($subTokens) + 1;
            } else {
                throw new SyntaxException('Unexpected token ' . $next->type());
            }
        }

        return $type;
    }

    protected function parseTupleType(array $tokens): Type
    {
        throw new NotSupportedException('tuple type is not supported yet');
    }

    /**
     * @param Token[] $tokens
     * @return Type[]
     */
    protected function parseGenericParameterTypes(array $tokens): array
    {
        $pos    = 0;
        $endPos = count($tokens) - 1;
        $types  = [];

        while ($pos <= $endPos) {
            $subTokens = $this->findSubTokens($tokens, [Token::COMMA], $pos);
            $types[]   = $this->parseInternal($subTokens);
            $pos       += count($subTokens) + 1;
        }

        return $types;
    }
}
