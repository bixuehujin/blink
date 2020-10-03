<?php

declare(strict_types=1);

namespace blink\typing;

/**
 * Class Tokenizer
 *
 * @package blink\typing
 */
class Tokenizer
{
    /**
     * @param string $definition
     * @return Token[]
     */
    public function tokenize(string $definition): array
    {
        $tokens = [];
        $len    = strlen($definition);
        $pos    = $i = 0;
        for (; $i < $len; $i++) {
            $char = $definition[$i];

            if (in_array($char, Token::CONTROL_TOKENS)) {
                if ($pos !== $i) {
                    $tokens[] = new Token(Token::TEXT, substr($definition, $pos, $i - $pos));
                }
                $tokens[] = new Token($char);
                $pos      = $i + 1;
            } elseif (preg_match('/\s/', $char)) {
                if ($pos !== $i) {
                    $tokens[] = new Token(Token::TEXT, substr($definition, $pos, $i - $pos));
                }
                $pos = $i + 1;
            } else {
                if ($i === $len - 1) {
                    $tokens[] = new Token(Token::TEXT, substr($definition, $pos, $i - $pos + 1));
                }
            }
        }

        return $tokens;
    }
}
