<?php

namespace blink\tests\session;

use blink\di\config\ConfigContainer;
use blink\di\Container;
use blink\session\Manager;
use blink\session\FileStorage;
use blink\session\Session;
use blink\session\SessionServiceProvider;
use blink\session\StorageContract;
use blink\tests\TestCase;
use Hyperf\Config\Config;

class SessionTest extends TestCase
{
    private string $sessionPath;

    public function setUp(): void
    {
        $this->sessionPath = __DIR__ . '/sessions';
        mkdir($this->sessionPath);

        parent::setUp();
    }

    public function tearDown(): void
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
        $container = new Container();

        $container->add(new SessionServiceProvider());
        $container->get(ConfigContainer::class)->set('session.path', $this->sessionPath);

        $container->alias(FileStorage::class, StorageContract::class);

        return $container->get(Manager::class);
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
