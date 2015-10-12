<?php

namespace blink\auth;


interface Contract
{
    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function validate(array $credentials = []);

    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function attempt(array $credentials = []);

    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function once(array $credentials = []);

    /**
     * Login the given user.
     *
     * @param Authenticatable $user
     * @return string the session id
     */
    public function login(Authenticatable $user);

    /**
     * Logout the given user.
     *
     * @param $sessionId
     * @return boolean
     */
    public function logout($sessionId);

    /**
     * @param $sessionId
     * @return Authenticatable|null
     */
    public function who($sessionId);
}
