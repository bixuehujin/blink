<?php

declare(strict_types=1);

namespace blink\auth\middleware;

use blink\http\Cookie;
use blink\http\Request;
use blink\http\Response;
use blink\session\Manager;
use blink\session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CookieAuthenticator
 *
 * @package blink\auth\middleware
 */
class CookieAuthenticator implements MiddlewareInterface
{
    //<<Inject()>>
    public Manager $session;
    //<<Inject('auth.cookie.session_key')>>
    public string $sessionKey = 'BLINK_SESSION_ID';
    //<<Inject('auth.cookie.params')>>
    public array $cookieParams = [];

    public function createNewCookie($session)
    {
        return new Cookie($this->cookieParams + [
                'name'     => $this->sessionKey,
                'value'    => $session->id,
                'httpOnly' => true,
            ]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        assert($request instanceof Request);
        $cookie = $request->cookies->get($this->sessionKey);

        if (!$cookie) {
            $session   = $this->session->put([]);
            $newCookie = $this->createNewCookie($session);
        } elseif ($session = $this->session->get($cookie->value)) {
            // noop
        } else {
            $session = $this->session->put(['id' => $cookie->value]);
        }

        $request->setSession($session);

        $response = $handler->handle($request);
        assert($response instanceof Response);

        if (isset($newCookie)) {
            $response->cookies->add($newCookie);
        }

        return $response;
    }
}
