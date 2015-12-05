请求处理
=======

接收输入
--------

Blink 中 `\blink\http\Request` 承载了所有的用户输入，我们可以方便的获取请求头、URL参数、请求数据等信息：

```php
use \bink\core\Object;
use \bink\http\Request;

class Controller extends Object
{
    public function index(Request $request)
    {
        $type = $request->params->get('type'); // 获取 Query 参数 type
        $params = $request->params->all(); // 获取所有 Query 参数

        $name = $request->body->get('name'); // 获取 Request Body 的 name 参数
        $body = $request->body->all(); // 获取整个 Request Body
    }
}
```

更多有用的方法请参考 `\blink\http\Request` 的[源代码及注释](/src/http/Request.php)。


返回数据
-------

Blink 中，Action 方法可以直接返回数据给客户端，支持返回字符串和数组类型：

```php
use \bink\core\Object;
use \bink\http\Request;

class Controller extends Object
{

    public function action1()
    {
        return 'this is a string'; // 直接返回字符串，原样输出到客户端。
    }

    public function action2()
    {
        return [
            'name' => 'foo'        // 返回数组，json_encode 后输出到客户端
        ]
    }
}
```


中间件(Middleware)
-----------------

Blink 支持 Request 和 Response 的中间件机制，通过为 Request 和 Response 编写中间件，我们可以很方便的实现
诸如权限认证和数据格式化之类的功能。

要使用中间件功能，我们只需要设置 Request 或 Response 的 `middleware` 属性即可。通过该属性，指定应用到 Request
或 Response 的全局中间件。下面的示例指定了对所有的请求应用 BasicAccess 中间件：

```php
    'request' => [
        'class' => \blink\http\Request::class,
        'middleware' => [
            blink\auth\middleware\BasicAccess::class,
        ],
    ],
```

除了在配置文件中指定应用到全局的中间件，我们也可以在处理请求的过程中(在中间件被执行之前)动态的指定需要应用的中间件。如：

```php
 response()->middleware(Cors::class, true);
```

这里我们通过 Response 的 `middleware()` 方法动态的添加了 `Cors` 这个中间件，其中第二参数表示是否吧该中间件放在执行栈的最前面。
通过动态指定中间件的方式，我们可以很方便的只对特定的 Controller 或 Action 应用中间件。


实现一个中间件
-----------

在 Blink 中，我们通过实现 `blink\core\MiddlewareContract` 接口可以很方便的实现一个中间件。要实现这个接口，我们只需要实现
一个 `handle()` 方法即可，这个方法的原型如下：

```php
public function handle($owner)
{

}
```

其中 `$owner` 代表该中间件所应用的的 Request 或 Response 对象。

同时，我们默认提供了 `blink\auth\middleware\BasicAccess` 中间件，用于 Http 的 BasicAuth 权限认证，
读者可以参考该中间件的实现了解更多。
