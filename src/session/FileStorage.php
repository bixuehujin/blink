<?php

namespace blink\session;

use blink\base\InvalidConfigException;
use blink\session\Contract as SessionContract;

/**
 * Class FileStorage
 *
 * @package blink\session
 */
class FileStorage implements  SessionContract
{
    public $path;

    public function init()
    {
        if (!$this->path || !file_exists($this->path) || !is_writable($this->path)) {
            throw new InvalidConfigException("The param: '{$this->path}' is invalid or not writable");
        }
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = [])
    {
        $id = md5(microtime(true) . uniqid('', true) . uniqid('', true));

        return $this->set($id, $attributes) ? $id : false;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (file_exists($this->path . '/' . $id)) {
            return new SessionBag(unserialize(file_get_contents($this->path . '/' . $id)));
        }
    }

    /**
     * @inheritDoc
     */
    public function set($id, $attributes)
    {
        return file_put_contents($this->path . '/' . $id, serialize($attributes)) !== false;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        if (file_exists($this->path . '/' . $id)) {
            return unlink($this->path . '/' . $id);
        } else {
            return false;
        }
    }
}