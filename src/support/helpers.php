<?php

use blink\di\Container;
use blink\core\InvalidConfigException;
use blink\core\HttpException;

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
    if (!Container::$instance) {
        Container::$instance = new Container();
    }

    if (is_string($type)) {
        return Container::$instance->get($type, $params);
    } elseif (is_array($type) && isset($type['class'])) {
        $class = $type['class'];
        unset($type['class']);
        return Container::$instance->get($class, $params, $type);
    } elseif (is_callable($type, true)) {
        return call_user_func($type, $params);
    } elseif (is_array($type)) {
        throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
    } else {
        throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
    }
}

/**
 * Helper function to get application instance or registered application services.
 *
 * @param null $service
 * @return \blink\core\Application
 */
function app($service = null)
{
    if ($service === null) {
        return Container::$app;
    } else {
        return Container::$app->get($service);
    }
}

/**
 * Helper function to get log service.
 *
 * @return \blink\log\Logger
 */
function logger()
{
    return Container::$app->get('log');
}

/**
 * Helper function to get session service.
 *
 * @return \blink\session\Contract
 */
function session()
{
    return Container::$app->get('session');
}

/**
 * Helper function to get auth service.
 *
 * @return \blink\auth\Contract
 */
function auth()
{
    return Container::$app->get('auth');
}

/**
 * Helper function to get current request.
 *
 * @return \blink\http\Request
 */
function request()
{
    return Container::$app->get('request');
}

/**
 * Helper function to get current response.
 *
 * @return \blink\http\Response
 */
function response()
{
    return Container::$app->get('response');
}


/**
 * Abort the current request.
 *
 * @param $status
 * @param string $message
 * @throws \blink\core\HttpException
 */
function abort($status, $message = null)
{
    throw new HttpException($status, $message);
}
