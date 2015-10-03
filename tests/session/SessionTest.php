<?php

namespace blink\tests\session;

use blink\session\Session;
use blink\session\FileStorage;
use blink\session\SessionBag;
use blink\tests\TestCase;

class SessionTest extends TestCase
{

    private  $sessionPath = __DIR__ . '/sessions';

    public function setUp()
    {
        mkdir($this->sessionPath);

        parent::setUp();
    }

    public function tearDown()
    {
        foreach(new \DirectoryIterator($this->sessionPath) as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }

        rmdir($this->sessionPath);

        parent::tearDown();
    }

    protected function createSession()
    {
        return make([
            'class' => Session::class,
            'storage' => [
                'class' => FileStorage::class,
                'path' => $this->sessionPath,
            ]
        ]);
    }

    public function testSimple()
    {
        $session = $this->createSession();

        $id = $session->put(['foo' => 'bar']);

        $this->assertEquals(32, strlen($id));

        $bag = $session->get($id);
        $this->assertInstanceOf(SessionBag::class, $bag);

        $bag->set('foo', 'bar');
        $this->assertTrue($session->set($id, $bag));
    }
}
