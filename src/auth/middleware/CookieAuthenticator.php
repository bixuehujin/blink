<?php

namespace blink\auth\middleware;

use blink\core\MiddlewareContract;
use blink\http\Cookie;
use blink\http\Request;

/**
 * Class CookieAuthenticator
 *
 * @package blink\auth\middleware
 */
class CookieAuthenticator implements MiddlewareContract
{
    public $sessionKey = 'BLINK_SESSION_ID';
    public $autoGeneration = false; // ??
    public $cookieParams = [];

    /**
     * @param Request $request
     */
    public function handle($request)
    {
        $cookie = $request->cookies->get($this->sessionKey);

        if (!$cookie) {
            return;
        }

        $session = $oldSession = session()->get($cookie->value);

        if (!$session && $this->autoGeneration) {
            $session = session()->put([]);
            $this->handleNewSession($session);
        }

        $request->setSession($session);

        if (!$oldSession) {
            return;
        }

        $user = auth()->who($oldSession);
        if (!$user) {
            return;
        }

        auth()->login($user, true);
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
