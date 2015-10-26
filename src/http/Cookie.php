<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;


use blink\core\Object;

/**
 * Class Cookie
 *
 * @package blink\http
 * @since 0.1.1
 */
class Cookie extends Object
{
    /**
     * The name of the cookie.
     *
     * @var string
     */
    public $name;

    /**
     * The value of the cookie.
     *
     * @var string
     */
    public $value = '';

    /**
     * The domain of the cookie.
     *
     * @var string
     */
    public $domain = '';

    /**
     * The timestamp at which the cookie expires, default to 0, meaning "until the browser is closed"
     *
     * @var int
     */
    public $expire = 0;

    /**
     * The path of the cookie.
     *
     * @var string
     */
    public $path = '/';

    /**
     * Whether cookie should be sent via secure connection
     *
     * @var bool
     */
    public $secure = false;

    /**
     * Whether the cookie should be accessible only through the HTTP protocol.
     *
     * @var bool
     */
    public $httpOnly = true;
}
