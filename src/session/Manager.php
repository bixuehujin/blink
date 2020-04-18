<?php

namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\BaseObject;
use blink\injector\ContainerAware;
use blink\injector\ContainerAwareTrait;
use blink\session\Contract as SessionContract;

/**
 * The Session Manager
 *
 * @package blink\session
 */
class Manager extends BaseObject implements SessionContract, ContainerAware
{
    use ContainerAwareTrait;

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
        if ($this->sessionClass !== Session::class
            && !is_subclass_of($this->sessionClass, Session::class)) {
            throw new InvalidConfigException(sprintf('The %s::$sessionClass config expects a subclass of "blink\session\Session" as its value'));
        }
    }

    protected function getStorage(): StorageContract
    {
        if (!$this->storage instanceof StorageContract) {
            $this->storage = $this->getContainer()->make2($this->storage);
            $this->storage->timeout($this->expires);
        }

        return $this->storage;
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

        $this->getStorage()->write($id, $attributes);

        $class = $this->sessionClass;
        return new $class($attributes, ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if ($id) {
            $data = $this->getStorage()->read($id);
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

        return $this->getStorage()->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return $this->getStorage()->destroy($id);
    }
}
