<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use blink\core\BaseObject;
use blink\core\InvalidParamException;

/**
 * Class Cookie
 *
 * @package blink\http
 * @since 0.2.0
 */
class Cookie extends BaseObject
{
    /**
     * The name of the cookie.
     */
    public string $name;

    /**
     * The value of the cookie.
     */
    public string $value = '';

    /**
     * The domain of the cookie.
     */
    public string $domain = '';

    /**
     * The timestamp at which the cookie expires, default to 0, meaning "until the browser is closed"
     */
    public int $expire = 0;

    /**
     * The path of the cookie.
     */
    public string $path = '/';

    /**
     * Whether cookie should be sent via secure connection
     */
    public bool $secure = false;

    /**
     * Whether the cookie should be accessible only through the HTTP protocol.
     */
    public bool $httpOnly = false;

    /**
     * The SameSite attribute of the cookie: Lax, Strict or None.
     */
    public ?string $sameSite = null;

    public function init()
    {
        if ($this->sameSite === null) {
            return;
        }

        if (!in_array($this->sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidParamException('Unsupported SameSite attribute: ' . $this->sameSite);
        }
    }

    public function toString(): string
    {
        $line = "{$this->name}={$this->value}";

        if ($this->expire > 0) {
            $line .= '; expires=' . gmdate('D, d-M-Y H:i:s', $this->expire) . ' GMT';
        }
        if ($this->path) {
            $line .= "; path={$this->path}";
        }
        if ($this->domain) {
            $line .= "; domain={$this->domain}";
        }

        if ($this->secure) {
            $line .= '; secure';
        }
        if ($this->httpOnly) {
            $line .= '; HttpOnly';
        }
        if ($this->sameSite) {
            $line .= "; SameSite={$this->sameSite}";
        }

        return $line;
    }
}
