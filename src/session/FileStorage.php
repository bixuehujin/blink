<?php

declare(strict_types=1);

namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\BaseObject;
use blink\session\Contract as SessionContract;

/**
 * Class FileStorage
 *
 * @package blink\session
 */
class FileStorage extends BaseObject implements StorageContract
{
    public ?string $path    = null;
    public int     $divisor = 1000;

    protected int $timeout;

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
    public function read(string $id): ?array
    {
        if (file_exists($this->path . '/' . $id)) {
            $content = file_get_contents($this->path . '/' . $id);
            if ($content === false) {
                return null;
            }
            return unserialize($content);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, array $data): bool
    {
        return file_put_contents($this->path . '/' . $id, serialize($data)) !== false;
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
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
        assert($this->path !== null);
        $iterator = new \DirectoryIterator($this->path);
        $now      = time();

        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->getMTime() < $now - $this->timeout) {
                if ($path = $file->getRealPath()) {
                    @unlink($path);
                }
            }
        }
    }
}
