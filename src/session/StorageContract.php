<?php

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
    public function read($id);

    /**
     * Write session data to storage.
     *
     * @param string $id
     * @param array $data
     * @return boolean
     */
    public function write($id, array $data);


    /**
     * Destroy session by id.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id);

    /**
     * @param integer $timeout
     */
    public function timeout($timeout);
}
