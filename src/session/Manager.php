<?php

declare(strict_types=1);


namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\BaseObject;
use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use blink\session\Contract as SessionContract;
use blink\di\attributes\Inject;

/**
 * The Session Manager
 *
 * @package blink\session
 */
class Manager extends BaseObject implements SessionContract, ContainerAware
{
    use ContainerAwareTrait;

    #[Inject]
    public StorageContract $storage;
    /**
     * How long the session should expires, defaults to 15 days.
     *
     * @var int
     */
    public int $expires = 1296000;

    public string $sessionClass = Session::class;

    public function init()
    {
        if ($this->sessionClass !== Session::class
            && !is_subclass_of($this->sessionClass, Session::class)) {
            throw new InvalidConfigException(sprintf('The %s::$sessionClass config expects a subclass of "blink\session\Session" as its value', get_class($this)));
        }
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = []): Session
    {
        if ($attributes instanceof Session) {
            $id         = $attributes->id;
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
    public function get(string $id): ?Session
    {
        if ($id) {
            $data = $this->storage->read($id);
            if ($data !== null) {
                $class = $this->sessionClass;
                return new $class($data, ['id' => $id]);
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $id, $attributes): bool
    {
        if ($attributes instanceof Session) {
            $attributes = $attributes->all();
        }

        return $this->storage->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        return $this->storage->destroy($id);
    }
}
