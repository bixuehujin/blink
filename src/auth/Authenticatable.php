<?php

namespace blink\auth;

interface Authenticatable
{
    /**
     * Find model by it's identifiers.
     *
     * @param mixed $id
     * @return static|null
     */
    public static function findIdentity(mixed $id);
    /**
     * Get the auth id that used to store in session.
     *
     * @return mixed
     */
    public function getAuthId(): mixed;
}
