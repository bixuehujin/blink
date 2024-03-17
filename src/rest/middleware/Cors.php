<?php

namespace blink\rest\middleware;

use blink\core\BaseObject;
use blink\http\Response;
use blink\http\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Cors
 *
 * @package blink\rest\middleware
 */
class Cors extends BaseObject implements MiddlewareInterface
{
    public array  $allowOrigins     = [];
    public string $allowMethods     = 'GET, PUT, POST, DELETE';
    public string $allowHeaders     = 'Authorization, Content-Type';
    public bool   $allowCredentials = false;
    public string $exposeHeaders    = 'Authorization';
    public int    $maxAge           = 86400;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($request->getMethod() === 'OPTIONS') {
            $emptyStream = new Stream("php://memory", 'rw+');
            $response = $response->withBody($emptyStream);
        }

        $origin = $request->getHeaderLine('Origin');
        if (!$origin) {
            return $response;
        }

        if (!$this->matchOrigin($origin, (array)$this->allowOrigins)) {
            return $response;
        }

        $headers = [
            'Vary'                          => 'Origin',
            'Access-Control-Allow-Origin'   => $origin,
            'Access-Control-Allow-Methods'  => $this->allowMethods,
            'Access-Control-Allow-Headers'  => $this->allowHeaders,
            'Access-Control-Expose-Headers' => $this->exposeHeaders,
            'Access-Control-Max-Age'        => $this->maxAge,
        ];

        if ($this->allowCredentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        if ($response instanceof Response) {
            $response->headers->add($headers);
        } else {
            foreach ($headers as $key => $value) {
                $response = $response->withHeader($key, (string)$value);
            }
        }

        return $response;
    }

    protected function matchOrigin(string $target, array $allowOrigins)
    {
        if (empty($allowOrigins)) {
            return true;
        }

        $target = parse_url($target, PHP_URL_HOST);
        assert(is_string($target));

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
