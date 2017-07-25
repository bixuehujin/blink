<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Blink Team
 * @license the MIT License
 */

namespace blink\tests\http;

use blink\http\Cookie;
use blink\tests\TestCase;

/**
 * Class CookieTest
 *
 * @package blink\tests\http
 */
class CookieTest extends TestCase
{
    public function testCookieString()
    {
        $cookie = new Cookie([
            'name' => 'foo',
            'value' => 'bar',
        ]);

        $this->assertEquals('foo=bar; path=/', $cookie->toString());

        $cookie = new Cookie([
            'name' => 'foo',
            'value' => 'bar',
            'httpOnly' => true,
            'secure' => true,
        ]);

        $this->assertEquals('foo=bar; path=/; secure; HttpOnly', $cookie->toString());
    }
}
