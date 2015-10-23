<?php

namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\Object;
use blink\session\Contract as SessionContract;

/**
 * Class FileStorage
 *
 * @package blink\session
 */
class FileStorage extends Object implements StorageContract
{
    public $path;
    public $divisor = 1000;

    protected $timeout;

    public function init()
    {
        if (!$this->path) {
            throw new InvalidConfigException("The parameter 'blink\\session\\FileStorage::path' must be configured");
        }

        if (!file_exists($this->path)) {
            @mkdir($this->path, 0777, true);
        }

        if (!file_exists($this->path) || !is_writable($this->path)) {
            throw new InvalidConfigException("The parameter 'blink\\session\\FileStorage::path': '{$this->path}' is invalid or not writable");
        }

        if (rand(0, $this->divisor) <= 0) {
            $this->gc();
        }
    }

    /**
     * @inheritDoc
     */
    public function read($id)
    {
        if (file_exists($this->path . '/' . $id)) {
            return unserialize(file_get_contents($this->path . '/' . $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function write($id, array $data)
    {
        return file_put_contents($this->path . '/' . $id, serialize($data)) !== false;
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

    /**
     * @inheritDoc
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
    }

    protected function gc()
    {
        $iterator = new \DirectoryIterator($this->path);
        $now = time();

        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->getMTime() < $now - $this->timeout) {
                @unlink($file->getRealPath());
            }
        }
    }
}
