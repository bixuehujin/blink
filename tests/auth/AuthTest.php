<?php

namespace blink\tests\auth;

use blink\auth\Auth;
use blink\auth\Authenticatable;
use blink\base\InvalidParamException;
use blink\base\Object;
use blink\http\Application;
use blink\testing\TestCase;
use blink\session\Session;
use blink\session\FileStorage;

/**
 * Class AuthTest
 *
 * @package blink\tests\auth
 */
class AuthTest extends TestCase
{
    private $sessionPath = '/tmp';

    public function createApplication()
    {
        return new Application([
            'services' => [
                'session' => [
                    'class' => Session::class,
                    'storage' => [
                        'class' => FileStorage::class,
                        'path' => $this->sessionPath,
                    ]
                ],
                'auth' => [
                    'class' => Auth::class,
                    'model' => TestUser::class,
                ],
            ]
        ]);
    }

    public function testAttempt()
    {
        $sessionId = auth()->attempt(['name' => 'user1', 'password' => 'user1']);
        $this->assertNotFalse($sessionId);

        $user = auth()->who($sessionId);
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals('user1', $user->name);
    }

    public function testOnce()
    {
        $user = auth()->once(['name' => 'user1', 'password' => 'user1']);

        $this->assertNotFalse($user);
        $this->assertInstanceOf(TestUser::class, $user);

        $user = auth()->once(['name' => 'user1', 'password' => 'invalid password']);
        $this->assertFalse($user);
    }
}

class TestUser extends Object implements Authenticatable
{
    public static $users = [
        ['id' => 1, 'name' => 'user1', 'password' => 'user1'],
        ['id' => 2, 'name' => 'user2', 'password' => 'user2']
    ];

    public $id;
    public $name;
    public $password;

    /**
     * @inheritDoc
     */
    public static function findIdentity($id)
    {
        if (is_numeric($id)) {
            $key = 'id';
            $value = $id;
        } else if (is_array($id) && isset($id['name'])) {
            $key = 'name';
            $value = $id['name'];
        } else {
            throw new InvalidParamException("The param: id is invalid");
        }

        foreach (static::$users as $user) {
            if ($user[$key] == $value) {
                return new static($user);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAuthId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
