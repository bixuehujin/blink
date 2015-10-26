<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use blink\core\Object;
use blink\support\BagTrait;

/**
 * Class CookieBag
 *
 * @package blink\http
 * @since 0.1.1
 */
class CookieBag extends Object
{
    use BagTrait;

    public function __construct(array $cookies = [], $config = [])
    {
        $this->replace($cookies);

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
}
