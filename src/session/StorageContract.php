<?php

declare(strict_types=1);

namespace blink\session;

/**
 * Interface StorageContract
 *
 * @package blink\session
 */
interface StorageContract
{
    /**
     * Read session by Session ID.
     *
     * @param string $id
     * @return null|array
     */
    public function read(string $id): ?array;

    /**
     * Write session data to storage.
     *
     * @param string $id
     * @param array $data
     * @return boolean
     */
    public function write(string $id, array $data): bool;


    /**
     * Destroy session by id.
     *
     * @param string $id
     * @return boolean
     */
    public function destroy(string $id): bool;

    /**
     * @param integer $timeout
     */
    public function timeout(int $timeout);
}
