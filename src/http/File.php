<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use RuntimeException;
use blink\core\BaseObject;
use Psr\Http\Message\UploadedFileInterface;

/**
 * File represents an uploaded file.
 *
 * @property string $extension The extension of uploaded file.
 * @property string $baseName The base name of uploaded file.
 *
 * @package blink\http
 * @author Jin Hu <bixuehujin@gmail.com>
 * @since 0.2.0
 */
class File extends BaseObject implements UploadedFileInterface
{
    public string $name;
    public string $tmpName;
    public string $type;
    public int $size;
    public int $error;

    private bool $_saved = false;
    private Stream $_stream;

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function getBaseName(): string
    {
        return pathinfo($this->name, PATHINFO_BASENAME);
    }

    public function hasError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    public function saveAs(string $path): bool
    {
        return copy($this->tmpName, $path);
    }

    /**
     * @inheritDoc
     */
    public function getStream()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->_saved) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }

        if (!$this->_stream) {
            $this->_stream = new Stream($this->tmpName);
        }

        return $this->_stream;
    }

    /**
     * @inheritDoc
     */
    public function moveTo($targetPath)
    {
        return $this->saveAs($targetPath);
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType()
    {
        return $this->type;
    }
}
