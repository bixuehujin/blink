路由与控制器
==========

路由
---

Blink 默认的路由配置位于 `src/http/routes.php` 文件中，该文件返回一个数组，包含应用所有的路由定义。
下面是一个简单的路由配置文件：

```php
<?php
return [
    ['GET', '/', function () {
        return 'hello world';
    }],
    ['GET', '/foo/bar', function () {
        return 'hello foo bar';
    }]
];
```

路由的定义也支持指定多个 HTTP 请求方法（HTTP Method），实例如下：

```php
return [
    [['GET', 'HEAD'], '/', function () {
        // 该路径下的 GET 和 HEAD 请求都将由该函数处理
        return 'hello world';
    }]
];
```


带参数的路由
----------

路由的定义也可以携带参数，我们使用`{param}` 的语法来定义一个参数，其中`param`是参数的名称，控制器函数需要按顺序接受
框架传递过来的参数。如下的例子中定义了 type 和 id 两个参数：

```php
return [
    ['GET', '/users/{type}/{id}', function ($type, $id) {
        // 路由中定义的参数可以直接在控制器函数或方法中获取
    }]
];
```

上面的例子没有对 type 和 id 参数做任何限制，实际上下面这些 URL 都能通过该路由的校验：

```
/users/foo/123
/users/321/bar
/users/foo/bar
```

但实际上我们可以只希望 `/users/foo/123` 通过检验，这时我们可以使用正则表达式限制每个参数的值，其对应的语法是 `{param:expression}`,
下面的路由定义就符合我们的预期：

```php
return [
    ['GET', '/users/{type:[a-zA-Z]+}/{id:\d+}', function ($type, $id) {
        // 现在的 type 就限定为字符串， id 限定为整数了
    }]
];
```

控制器
-----

控制器函数除了上文中使用的匿名函数，更常见的是使用类的方法。我们使用 `ClassName@method` 这样的语法指定类方法作为控制器函数，如：

```php
return [
    ['GET', '/', '/app/http/controllers/IndexController@index']
];
```

该示例中我们采用了类的绝对命名空间，这个看起来会比较繁琐，这是我们可以结合 `src/config/app.php` 中的 `controllerNamespace`
配置，采用相对的命名空间格式，简化代码。结合两者，下面示例达到的效果将完全一致：

src/config/app.php
```php
return [
    'controllerNamespace' => '\app\http\controllers',
];
```

src/http/routes.php
```php
return [
    ['GET', '/', 'IndexController@index']
];
```


依赖注入
-------

Blink 支持控制器的构造函数和普通方法两种注入方式。通过依赖注入，我们可以很方便的把需要的对象拿来使用，而不用关心这些对象是怎么创建的，
框架本身自然会很好的处理好对象的创建。下面是一个简单的控制器注入案例：

```php
use blink\core\Object;
use blink\http\Request;

class Controller extends Object
{
    /**
     * 这里通过构造函数注入 Request 对象
     */
    public function __construct(Request $request, $config = [])
    {

        parent::__construct();
    }

    /**
     * 这里在普通方法中注 Request 对象
     *
     * @param $id 路由参数 id 的值，注意路由参数需要放在参数列表的前面
     * @param $request 注入的 Request 对象
     */
    public function index($id, Request $request)
    {

    }
}

```
