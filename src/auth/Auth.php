<?php

namespace blink\auth;

use blink\auth\middleware\CookieAuthenticator;
use blink\core\BaseObject;
use blink\auth\Contract as AuthContract;
use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use blink\session\Session;

/**
 * Class Auth
 *
 * @package blink\auth
 */
class Auth extends BaseObject implements AuthContract, ContainerAware
{
    use ContainerAwareTrait;

    /**
     * The class that implements Authenticatable interface.
     *
     * @var \blink\auth\Authenticatable
     */
    public $model;

    /**
     * @inheritDoc
     */
    public function validate(array $credentials = [])
    {
        $class = $this->model;

        $password = isset($credentials['password']) ? $credentials['password'] : null;
        unset($credentials['password']);

        $user = $class::findIdentity($credentials);

        return $user && $user->validatePassword($password) ? $user : false;
    }

    /**
     * @inheritDoc
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->validate($credentials);
        if ($user) {
            $this->login($user, false);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function once(array $credentials = [])
    {
        $user = $this->validate($credentials);
        if ($user) {
            $this->login($user, true);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function login(Authenticatable $user, $once = false)
    {
        $request = request();

        if (!$once) {
            $session = session()->put(['auth_id' => $user->getAuthId()]);
            $request->session = $session;

            foreach ($request->middleware as $middleware) {
                $middleware = $this->getContainer()->make2($middleware);
                if ($middleware instanceof CookieAuthenticator) {
                    $middleware->createNewCookie($session);
                    break;
                }
            }
        }

        $request->user($user);
    }

    /**
     * @inheritDoc
     */
    public function logout($sessionId)
    {
        return session()->destroy($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function who($sessionId)
    {
        $class = $this->model;

        if ($sessionId instanceof Session) {
            $bag = $sessionId;
        } else {
            $bag = session()->get($sessionId);
        }

        if ($bag && ($authId = $bag->get('auth_id')) !== null) {
            return $class::findIdentity($authId);
        }
    }
}
