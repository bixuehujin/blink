<?php

declare(strict_types=1);

namespace blink\session;

use blink\core\InvalidConfigException;
use blink\core\BaseObject;
use blink\session\Contract as SessionContract;
use blink\di\attributes\Inject;
use DirectoryIterator;

/**
 * Class FileStorage
 *
 * @package blink\session
 */
class FileStorage extends BaseObject implements StorageContract
{
    #[Inject('session.path', 'setPath')]
    protected string  $path;
    public int        $divisor = 10000;

    protected int $timeout;

    public function setPath(string $path)
    {
        if (!file_exists($path)) {
            @mkdir($path, 0777, true);
        }

        if (!file_exists($path) || !is_writable($path)) {
            throw new InvalidConfigException("The parameter 'blink\\session\\FileStorage::path': '{$this->path}' is invalid or not writable");
        }

        $this->path = $path;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): ?array
    {
        $this->gc();

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

    protected function gc(): void
    {
        if (rand(0, $this->divisor) > 0) {
            return;
        }

        $iterator = new DirectoryIterator($this->path);
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
