<?php

namespace blink\auth;

use blink\session\Session;

/**
 * Interface used for `auth` service, all `auth` service should implement the interface.
 *
 * @package blink\auth
 */
interface Contract
{
    /**
     * Validates the given credentials against the underlying storage.
     *
     * @param array $credentials Array contains the username/email and password to validate.
     * @return Authenticatable|false An Authenticatable instance will be returned if success, false otherwise.
     */
    public function validate(array $credentials = []);

    /**
     * Attempt to validate the given credentials and login.
     *
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function attempt(array $credentials = []);

    /**
     * Similar with attempt(), but no session will generated.
     *
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function once(array $credentials = []);

    /**
     * Login the given user. if $once is not true, a new session will be generated, one can access the new session
     * through `$request->session`.
     *
     * @param Authenticatable $user
     * @param boolean $once Login just for once, no session will be stored.
     */
    public function login(Authenticatable $user, $once = false);

    /**
     * Destroy session by given sessionId, this will make the corresponding user logout.
     *
     * @param $sessionId
     * @return boolean
     */
    public function logout($sessionId);

    /**
     * Returns the user that associated with given sessionId.
     *
     * @param string|Session $sessionId
     * @return Authenticatable|null
     */
    public function who($sessionId);
}
