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

更多有用的方法请参考 `\blink\http\Request` 的[源代码及注释](src/http/Request.php)。


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

另外，Request 和 Response 的中间件架构也在计划中，未来会提供更多的方式来对输入输出的数据进行格式化。
