<?php

use blink\di\Container;
use blink\core\HttpException;

/**
 * Helper function to get application instance or registered application services.
 *
 * @param null $service
 * @return \blink\di\Container
 */
function app($service = null)
{
    if ($service === null) {
        return Container::$global;
    } else {
        return Container::$global->get($service);
    }
}

/**
 * Returns the configuration value by it's name.
 *
 * @param string $name
 * @return mixed
 */
function config(string $name): mixed
{
    return Container::$global->get($name);
}

/**
 * Helper function to get log service.
 *
 * @return \blink\logging\Logger
 */
function logger()
{
    return Container::$global->get('log');
}

/**
 * Helper function to get session service.
 *
 * @return \blink\session\Contract
 */
function session()
{
    return Container::$global->get('session');
}

/**
 * Helper function to get auth service.
 *
 * @return \blink\auth\Contract
 */
function auth()
{
    return Container::$global->get('auth');
}

/**
 * Helper function to get current request.
 *
 * @return \blink\http\Request
 */
function request()
{
    return Container::$global->get(\blink\http\Request::class);
}

/**
 * Helper function to get current response.
 *
 * @return \blink\http\Response
 */
function response()
{
    return Container::$global->get(\blink\http\Response::class);
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

if (!function_exists('env')) {
    /**
     * Returns env configuration by it's name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function env($name, $default = null)
    {
        $value = getenv($name);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        return $value;
    }
}
