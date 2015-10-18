认证与授权
========

Blink 提供了一套轻量级的*认证授权框架*，通过这套框架我们可以更加方便的在我们的应用中实现认证与授权的系列功能。

在 Blink 中，认证特性是由 `auth` 服务组件来完成的，我们可以通过 `auth()` 辅助函数来获取该服务的实例。为了让 `auth` 服务知道如何查找一个
用户并验证其密码的正确性，我们首先需要定义一个 User Identity 类来告诉 `auth` 服务这些信息：


定义 User Identity
-----------------

为了定义一个 User Identity 类，我们需要实现 `blink\auth\Authenticatable` 接口，下面的例子展示了如何利用静态用户数据定义 User Identiry：

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
     * 通过用户的唯一标志查找用户，例如 主键、邮箱
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
     * 返回该用户的 Auth ID，用于存储到 Session 中唯一标志这个用户
     */
    public function getAuthId()
    {
        return $this->id;
    }

    /**
     * 检查用户的密码是否与用户输入相匹配
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
```

User Identity 定义好之后，我们需要配置 `auth` 服务，设置 `model` 属性告诉 `auth` 服务 User Identity 是怎样定义的：

```php
'auth' => [
    'class' => 'blink\auth\Auth',
    'model' => 'app\User',
],
```

用户认证
-------

只要 User Identity 定义并且配置好，我们就可以通过用户输入的用户名和密码来认证用户了，下面是例子：

```php
$creditials = ['email' => 'foo@bar.com', 'password' => 123];

// 通过给定的用户名和密码进行用户认证
$user = auth()->attempt($creditials);

// 进行用户认证但是不启用 Session
$user = auth()->once($creditials);
```

如果采用 `auth()->attempt()` 来认证用户，`auth` 服务会利用 `session` 服务来为认证的用户存储必要的 Session 数据，所以我们需要配置好
`session` 服务以获取期望的结果。


用户授权
-------

授权是检查一个用户具有足够权限做某事的过程，Blink 中，该功能由  `blink\http\Request` 类实现，下面是一个简单的例子：

```php
use blink\core\Object;
use blink\http\Request;

class Controller extends Object
{
    public function actionFoo(Request $request)
    {
        if (!$requst->guest()) {
            $user = $requst->user(); // 获取当前授权成功的用户
        }
    }
}

```

目前，Blink 默认采用 `X-Session-Id` Http 头来传输 Session Id。当然，这也是可以配置的，我们可以通过设置 `blink\http\Request`
的 `sessionKey` 属性来改变这个行为，关于如何设置该属性，请查看对应类实现的注释。
