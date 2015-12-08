<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use ArrayIterator;
use IteratorAggregate;
use blink\core\Object;

/**
 * Class CookieBag
 *
 * @package blink\http
 * @since 0.2.0
 */
class CookieBag extends Object implements IteratorAggregate
{
    private $cookies;

    public function __construct(array $cookies = [], $config = [])
    {
        $this->cookies = static::normalize($cookies);

        parent::__construct($config);
    }

    public static function normalize(array $cookies)
    {
        foreach ($cookies as $name => $value) {
            if (!$value instanceof Cookie) {
                $cookies[$name] = new Cookie(['name' => $name, 'value' => $value]);
            }
        }

        return $cookies;
    }

    /**
     * Returns a cookie by name.
     *
     * @param $name
     * @return null
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
    public function add(Cookie $cookie)
    {
        $this->cookies[$cookie->name] = $cookie;
    }

    /**
     * Returns whether a cookie is exists.
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Remove a cookie by name.
     *
     * @param $name
     */
    public function remove($name)
    {
        unset($this->cookies[$name]);
    }

    public function count()
    {
        return count($this->cookies);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->cookies);
    }
}
