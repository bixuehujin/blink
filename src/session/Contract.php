<?php

declare(strict_types=1);

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
     * @param Session|array $attributes
     * @return Session The newly created session object
     */
    public function put($attributes = []): Session;

    /**
     * Get a session by session id.
     *
     * @param string $id
     * @return Session|null
     */
    public function get(string $id): ?Session;


    /**
     * Set session with new attributes.
     *
     * @param string $id
     * @param Session|array $attributes
     * @return boolean
     */
    public function set(string $id, $attributes): bool;

    /**
     * Destroy specified session.
     *
     * @param string $id
     * @return boolean
     */
    public function destroy(string $id): bool;
}
