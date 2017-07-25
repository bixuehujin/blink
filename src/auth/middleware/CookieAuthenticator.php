<?php

namespace blink\auth\middleware;

use blink\core\MiddlewareContract;
use blink\http\Cookie;
use blink\http\Request;
use blink\session\Session;

/**
 * Class CookieAuthenticator
 *
 * @package blink\auth\middleware
 */
class CookieAuthenticator implements MiddlewareContract
{
    public $sessionKey = 'BLINK_SESSION_ID';
    public $cookieParams = [];

    /**
     * @param Request $request
     */
    public function handle($request)
    {
        $cookie = $request->cookies->get($this->sessionKey);

        if (!$cookie) {
            $session = session()->put([]);
            $this->handleNewSession($session);
        } elseif ($session = session()->get($cookie->value)) {
            // noop
        } else {
            $session = session()->put(new Session([], ['id' => $cookie->value]));
        }

        $request->setSession($session);
    }

    protected function handleNewSession($session)
    {
        $cookie = new Cookie($this->cookieParams + [
            'name' => $this->sessionKey,
            'value' => $session->id,
            'httpOnly' => true,
        ]);

        response()->cookies->add($cookie);
    }
}
