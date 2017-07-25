<?php

namespace blink\auth\middleware;

use blink\core\MiddlewareContract;
use blink\http\Request;

/**
 * BasicAccess middleware.
 *
 * @package blink\auth\middleware
 */
class BasicAccess implements MiddlewareContract
{
    /**
     * The user identity name that used to authenticate.
     *
     * @var string
     */
    public $identity = 'name';

    /**
     * @param Request $owner
     */
    public function handle($owner)
    {
        $value = $owner->headers->first('Authorization');
        if (!$value) {
            return;
        }

        $parts = preg_split('/\s+/', $value);
        if (count($parts) < 2 && strtolower($parts[0]) !== 'basic') {
            return;
        }

        list($username, $password) = explode(':', base64_decode($parts[1], true));

        auth()->attempt([
            $this->identity => $username,
            'password' => $password,
        ]);
    }
}
