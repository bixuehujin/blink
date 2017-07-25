<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use blink\core\Object;

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
class File extends Object
{
    public $name;
    public $tmpName;
    public $type;
    public $size;
    public $error;

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
        return $this->error !== UPLOAD_ERR_OK;
    }

    public function saveAs($path)
    {
        return copy($this->tmpName, $path);
    }
}
