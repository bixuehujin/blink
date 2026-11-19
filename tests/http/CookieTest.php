<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Blink Team
 * @license the MIT License
 */

namespace blink\tests\http;

use blink\core\InvalidParamException;
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

    public function testSameSiteAttribute()
    {
        $cookie = new Cookie([
            'name' => 'foo',
            'value' => 'bar',
            'httpOnly' => true,
            'sameSite' => 'Lax',
        ]);

        $this->assertEquals('Lax', $cookie->sameSite);
        $this->assertEquals('foo=bar; path=/; HttpOnly; SameSite=Lax', $cookie->toString());
    }

    public function testExpireIsSerializedInGmt()
    {
        $cookie = new Cookie([
            'name' => 'foo',
            'value' => 'bar',
            'expire' => 1466694566,
        ]);

        $this->assertEquals('foo=bar; expires=Thu, 23-Jun-2016 15:09:26 GMT; path=/', $cookie->toString());
    }

    public function testInvalidSameSiteAttribute()
    {
        $this->expectException(InvalidParamException::class);

        new Cookie([
            'name' => 'foo',
            'value' => 'bar',
            'sameSite' => 'sometimes',
        ]);
    }
}
