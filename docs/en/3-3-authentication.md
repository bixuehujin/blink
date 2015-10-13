Authentication and Authorization
================================

Blink provides an abstract layer for authentication and authorization, by utilizing the abstract layer, it would be much
easier to implement authentication and authorization functions in Blink application.

In Blink, authentication feature is provided by `auth` application service which can be access through `auth()` helper
function, in order to let `auth` service known how to lookup a user from database and validate its password, we should first
define a user identity class that known everything about this.

Defining User Identity
----------------------

To define a user identity class, we just need to implement the `blink\auth\Authenticatable` interface. The following example
shows how can we implement a user identity using static user data:

```php
namespace app;

class User extends Object implements Authenticatable
{
    public static $users = [
        ['id' => 1, 'name' => 'user1', 'password' => 'user1'],
        ['id' => 2, 'name' => 'user2', 'password' => 'user2']
    ];

    public $id;
    public $name;
    public $password;

    /**
     * Find a user by its identifier, such primary key and email address
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
     * Returns the auth id of the identity, this will be stored in session to identify the user.
     */
    public function getAuthId()
    {
        return $this->id;
    }

    /**
     * Checks whether given password is valid.
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
```

After this, we need configure the `auth` service to tell it what identity class should be used. We can simply accomplish
this by setting the `model` property of `auth` service:

```php
'auth' => [
    'class' => 'blink\auth\Auth',
    'model' => 'app\User',
],
```

Authenticating Users
--------------------

Once user identity was implemented and configured, we can authenticate a user via its credentials, here is the example:

```php
$creditials = ['email' => 'foo@bar.com', 'password' => 123];

// authenticating user through givin credentials
$user = auth()->attempt($creditials);

// authenticating user through givin credentials once without session storage
$user = auth()->once($creditials);
```

If you are using `auth()->attempt()` to authenticate user, auth service will utilize session service to store session
information for the authenticated user and so that you need configure session service property to make it works as expected.

Authorizing Users
-----------------

Authorization is the process of verifying that a user has enough permissions to do something. In Blink, this function
is implemented by `blink\http\Request` class, here is the example:

```php
use blink\core\Object;
use blink\http\Request;

class Controller extends Object
{
    public function actionFoo(Request $request)
    {
        if (!$requst->guest()) {
            $user = $requst->user(); // accessing the authorized user
        }
    }
}

```

Currently, Blink utilize http header `X-Session-Id` to transfer session id by default, if you do not want this, you can
redefine the behavior by setting the `sessionKey` property of `blink\http\Request`, please checkout the corresponding
comments of the class for more detailed information.
