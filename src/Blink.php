<?php
/**
 * Created by PhpStorm.
 * User: hujin
 * Date: 15-9-4
 * Time: 上午3:21
 */

namespace blink;

use blink\base\InvalidConfigException;
use blink\di\Container;

/**
 * Class Blink
 * @package blink\Blink
 */
class Blink
{
    public static $container;
    public static $app;

    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return call_user_func($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
        }
    }
}

Blink::$container = new Container();

/**
 * Helper function to get application instance or registered application services.
 *
 * @param null $service
 * @return mixed
 */
function app($service = null)
{
    if ($service === null) {
        return Blink::$app;
    } else {
        return Blink::$app->get($service);
    }
}

/**
 * Shortcut helper function to create object via Object Configuration.
 *
 * @param $type
 * @param array $params
 * @return mixed
 * @throws InvalidConfigException
 */
function make($type, $params = [])
{
    return Blink::createObject($type, $params);
}

