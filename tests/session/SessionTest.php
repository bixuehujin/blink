<?php

namespace blink\tests\session;

use blink\session\Manager;
use blink\session\FileStorage;
use blink\session\Session;
use blink\tests\TestCase;

class SessionTest extends TestCase
{
    private $sessionPath;

    public function setUp()
    {
        $this->sessionPath = __DIR__ . '/sessions';
        mkdir($this->sessionPath);

        parent::setUp();
    }

    public function tearDown()
    {
        foreach (new \DirectoryIterator($this->sessionPath) as $file) {
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
            'class' => Manager::class,
            'storage' => [
                'class' => FileStorage::class,
                'path' => $this->sessionPath,
            ]
        ]);
    }

    public function testSimple()
    {
        $manager = $this->createSession();

        $session = $manager->put(['foo' => 'bar']);

        $this->assertEquals(32, strlen($session->id));

        $bag = $manager->get($session->id);
        $this->assertInstanceOf(Session::class, $bag);

        $bag->set('foo', 'bar');
        $this->assertTrue($manager->set($session->id, $bag));
    }
}
