<?php

namespace blink\auth;

/**
 * Interface Authenticatable
 *
 * @package blink\auth
 */
interface Authenticatable
{
    /**
     * Find model by it's identifiers.
     *
     * @param mixed $id
     * @return static
     */
    public static function findIdentity($id);

    /**
     * Get the auth id that used to store in session.
     *
     * @return mixed
     */
    public function getAuthId();

    /**
     * Check whether the given password is correct.
     *
     * @param $password
     * @return boolean
     */
    public function validatePassword($password);
}
