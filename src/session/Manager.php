<?php

namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\Object;
use blink\session\Contract as SessionContract;

/**
 * The Session Manager
 *
 * @package blink\session
 */
class Manager extends Object implements SessionContract
{
    /**
     * The backend session storage.
     *
     * @var array|StorageContract
     */
    public $storage;
    /**
     * How long the session should expires, defaults to 15 days.
     *
     * @var int
     */
    public $expires = 1296000;

    public $sessionClass = Session::class;

    public function init()
    {
        if (!$this->storage instanceof StorageContract) {
            $this->storage = make($this->storage);
        }

        $this->storage->timeout($this->expires);

        if ($this->sessionClass !== Session::class
            && !is_subclass_of($this->sessionClass, Session::class)) {
            throw new InvalidConfigException(sprintf('The %s::$sessionClass config expects a subclass of "blink\session\Session" as its value'));
        }
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = [])
    {
        if ($attributes instanceof Session) {
            $id = $attributes->id;
            $attributes = $attributes->all();
        }

        if (!isset($id)) {
            $id = md5(microtime(true) . uniqid('', true) . uniqid('', true));
        }

        $this->storage->write($id, $attributes);

        $class = $this->sessionClass;
        return new $class($attributes, ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if ($id) {
            $data = $this->storage->read($id);
            if ($data !== null) {
                $class = $this->sessionClass;
                return new $class($data, ['id' => $id]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function set($id, $attributes)
    {
        if ($attributes instanceof Session) {
            $attributes = $attributes->all();
        }

        return $this->storage->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return $this->storage->destroy($id);
    }
}
