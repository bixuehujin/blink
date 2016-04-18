<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use RuntimeException;
use blink\core\Object;
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
class File extends Object implements UploadedFileInterface
{
    public $name;
    public $tmpName;
    public $type;
    public $size;
    public $error;

    private $_saved = false;
    private $_stream;

    public function getExtension()
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function getBaseName()
    {
        return pathinfo($this->name, PATHINFO_BASENAME);
    }

    public function hasError()
    {
        return $this->error != UPLOAD_ERR_OK;
    }

    public function saveAs($path)
    {
        if ($this->_saved) {

        }
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
