<?php

namespace blink\session;

use blink\core\Object;
use blink\session\Contract as SessionContract;

/**
 * Class Session
 *
 * @package blink\session
 */
class Session extends Object implements SessionContract
{
    /**
     * The backend session storage.
     *
     * @var array|SessionContract
     */
    public $storage;
    /**
     * How long the session should expires, defaults to 15 days.
     *
     * @var int
     */
    public $expires = 1296000;

    public function init()
    {
        if (!$this->storage instanceof SessionContract) {
            $this->storage = make($this->storage);
        }
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = [])
    {
        if ($attributes instanceof SessionBag) {
            $attributes = $attributes->all();
        }

        return $this->storage->put($attributes);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->storage->get($id);
    }

    /**
     * @inheritDoc
     */
    public function set($id, $attributes)
    {
        if ($attributes instanceof SessionBag) {
            $attributes = $attributes->all();
        }

        return $this->storage->set($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return $this->storage->destroy($id);
    }
}
