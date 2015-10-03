<?php

namespace blink\auth;

use blink\base\Object;
use blink\auth\Contract as AuthContract;

/**
 * Class Auth
 *
 * @package blink\auth
 */
class Auth extends Object implements AuthContract
{
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
            return session()->put(['auth_id' => $user->getAuthId()]);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function once(array $credentials = [])
    {
        return $this->validate($credentials);
    }

    /**
     * @inheritDoc
     */
    public function who($sessionId)
    {
        $class = $this->model;

        if ($bag = session()->get($sessionId)) {
            return $class::findIdentity($bag->get('auth_id'));
        }
    }
}
