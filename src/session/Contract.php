<?php

namespace blink\session;

/**
 * Interface Contract
 * 
 * @package blink\session
 */
interface Contract
{
    /**
     * Put a new session into storage.
     *
     * @param array $attributes
     * @return string The session id
     */
    public function put($attributes = []);

    /**
     * Get a session by session id.
     *
     * @param $id
     * @return SessionBag
     */
    public function get($id);


    /**
     * Set session with new attributes.
     *
     * @param $id
     * @param $attributes
     * @return boolean
     */
    public function set($id, $attributes);

    /**
     * Destroy specified session.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id);
}