<?php

namespace blink\http\middleware;

use blink\core\BaseObject;
use blink\core\MiddlewareContract;

/**
 * Class Cors
 *
 * @package blink\http\middleware
 */
class Cors extends BaseObject implements MiddlewareContract
{
    public $allowOrigins = [];
    public $allowMethods = 'GET, PUT, POST, DELETE';
    public $allowHeaders = 'Authorization, Content-Type';
    public $allowCredentials = false;
    public $exposeHeaders = 'Authorization';
    public $maxAge = 86400;

    /**
     * @param \blink\http\Response $response
     */
    public function handle($response)
    {
        if (request()->is('OPTIONS')) {
            $response->data = '';
        }

        $origin = request()->headers->first('Origin');
        if (!$origin) {
            return;
        }

        if (!$this->matchOrigin($origin, (array)$this->allowOrigins)) {
            return;
        }

        $headers = [
            'Vary' => 'Origin',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => $this->allowMethods,
            'Access-Control-Allow-Headers' => $this->allowHeaders,
            'Access-Control-Expose-Headers' => $this->exposeHeaders,
            'Access-Control-Max-Age' => $this->maxAge,
        ];

        if ($this->allowCredentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        $response->headers->add($headers);
    }

    protected function matchOrigin($target, $allowOrigins)
    {
        if (empty($allowOrigins)) {
            return true;
        }

        $target = parse_url($target, PHP_URL_HOST);

        foreach ($allowOrigins as $origin) {
            $origin = strtr($origin, [
                '*' => '[a-zA-Z0-9-]+',
                '.' => '\.',
            ]);

            if (preg_match("/^$origin$/", $target)) {
                return true;
            }
        }

        return false;
    }
}
