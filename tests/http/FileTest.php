<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Blink Team
 * @license the MIT License
 */

namespace blink\tests\http;

use blink\http\File;
use blink\http\FileBag;
use blink\tests\TestCase;

/**
 * Class FileTest
 *
 * @package blink\tests\http
 */
class FileTest extends TestCase
{
    protected function fakeFile()
    {
        return make([
            'class' => File::class,
            'name' => 'test.jpg',
            'tmpName' => '/tmp/tmp.' . uniqid(),
            'size' => 1024,
            'type' => 'image/jpeg',
            'error' => 0
        ]);
    }

    public function testAttribute()
    {
        $file = $this->fakeFile();

        $this->assertEquals('jpg', $file->extension);
        $this->assertEquals('test.jpg', $file->baseName);
        $this->assertFalse($file->hasError());
    }

    public function testSaveAs()
    {
        $file = $this->fakeFile();
        touch($file->tmpName);
        $target = __DIR__ . '/uploaded';
        $file->saveAs($target);
        $this->assertTrue(file_exists($target));
        unlink($target);
    }

    public function testBag()
    {
        $files['foo'] = $this->fakeFile();
        $files['bar[0]'] = $this->fakeFile();
        $files['bar[1]'] = $this->fakeFile();

        $bag = new FileBag($files);

        $this->assertNull($bag->first('not_exists'));
        $this->assertInstanceOf(File::class, $bag->first('bar'));
        $this->assertInstanceOf(File::class, $bag->first('foo'));
        $this->assertInstanceOf(File::class, $bag->get('bar')[0]);
        $this->assertInstanceOf(File::class, $bag->get('foo')[0]);
    }
}
