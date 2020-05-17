<?php

declare(strict_types=1);

namespace blink\auth\middleware;

use blink\auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * BasicAccess middleware.
 *
 * @package blink\auth\middleware
 */
class BasicAccess implements MiddlewareInterface
{
    //<<Inject()>>
    public Auth $auth;
    /**
     * The user identity name that used to authenticate.
     *
     * @var string
     */
    //<<Inject('auth.basic-access.identity')>>
    public string $identity = 'name';

    protected function authorize(ServerRequestInterface $request): ServerRequestInterface
    {
        $value = $request->getHeader('Authorization');
        if (!$value) {
            return $request;
        }

        $parts = preg_split('/\s+/', $value[0]);
        assert($parts !== false);
        if (count($parts) < 2 && strtolower($parts[0]) !== 'basic') {
            return $request;
        }

        $parts = explode(':', (string) base64_decode($parts[1], true));
        if (! $parts) {
            return $request;
        }
        list($username, $password) = $parts;

        $this->auth->attempt([
            $this->identity => $username,
            'password'      => $password,
        ]);

        return $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->authorize($request);

        return $handler->handle($request);
    }
}
