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
     * @return string|false
     */
    public function attempt(array $credentials = []);

    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function once(array $credentials = []);

    /**
     * @param $sessionId
     * @return Authenticatable|null
     */
    public function who($sessionId);
}