<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use ArrayIterator;
use IteratorAggregate;
use blink\core\BaseObject;
use Traversable;

/**
 * Class CookieBag
 *
 * @package blink\http
 * @since 0.2.0
 */
class CookieBag extends BaseObject implements IteratorAggregate
{
    private $cookies;

    public function __construct(array $cookies = [], $config = [])
    {
        $this->cookies = static::normalize($cookies);

        parent::__construct($config);
    }

    public function replace(array $cookies): void
    {
        $this->cookies = self::normalize($cookies);
    }

    public static function normalize(array $cookies): array
    {
        foreach ($cookies as $name => $value) {
            if (!$value instanceof Cookie) {
                $cookies[$name] = new Cookie(['name' => $name, 'value' => $value]);
            }
        }

        return $cookies;
    }

    /**
     * Returns all cookies.
     *
     * @return Cookie[]
     * @since 0.3.0
     */
    public function all(): array
    {
        return $this->cookies;
    }

    /**
     * Returns a cookie by name.
     *
     * @param string $name
     * @return Cookie|null
     */
    public function get($name)
    {
        return isset($this->cookies[$name]) ? $this->cookies[$name] : null;
    }

    /**
     * Add a cookie to the bag.
     *
     * @param Cookie $cookie
     */
    public function add(Cookie $cookie): void
    {
        $this->cookies[$cookie->name] = $cookie;
    }

    /**
     * Returns whether a cookie is exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Remove a cookie by name.
     *
     * @param string $name
     */
    public function remove(string $name)
    {
        unset($this->cookies[$name]);
    }

    public function count(): int
    {
        return count($this->cookies);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cookies);
    }
}
