Routing and Controllers
=======================

Routing
-------

By default, Blink's routing configuration is located at `src/http/routes.php`, this file should
return a array that contains all route definitions for the application.

The following is a simple route definition:


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

It is also possible to specify multiple HTTP Verb for a single definition, for example:


```php
return [
    [['GET', 'HEAD'], '/', function () {
        // both GET and HEAD requests to '/'  will be handled by this function
        return 'hello world';
    }]
];
```


Routing with Parameters
-----------------------

We can define a route with parameters by using `{param}` syntax , the `param` is the name. When a route have parameters 
defined, the handler of the route should accept all its parameters by the order when defined. The following example 
defined two parameters with `type` and `id`:


```php
return [
    ['GET', '/users/{type}/{id}', function ($type, $id) {
        // we can access all the defined parameters in its handler function by the order when defined
    }]
];
```

The simple example above is already working petty well for us, and all the following URL are valid:

```
/users/foo/123
/users/321/bar
/users/foo/bar
```

But in actually, we probably want more limitations on the type of the parameters, lets say `type` should be string 
and `id` should be integer, what should we do?

In Blink, we can also define a route by using regex patterns, the syntax is `{param:expression}`. With this syntax we
define a route like the following to fit our need:


```php
return [
    ['GET', '/users/{type:[a-zA-Z]+}/{id:\d+}', function ($type, $id) {
        // by now, type will be a valid string and id will be an valid integer
    }]
];
```

Controller
----------

Except the anonymous function we used before as the route handler, we can also use syntax like `ClassName@method` to 
specify class method as route handler. Here is the example:


```php
return [
    ['GET', '/', '/app/http/controllers/IndexController@index']
];
```

In the example above, the controller is a fully qualified class name, but in practice, it is possible to combine
the `controllerNamespace` configuration from `src/config/app.php` to simplify our route definition, the following 
example will behave the same result exactly:


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


Dependency Injection
--------------------

The controllers in Blink support both constructor and method injection, we can inject what ever we want into our controller
very conveniently. The following is a simple example showing how dependency injection will work in controller classes:


```php
use blink\core\Object;
use blink\http\Request;

class Controller extends Object
{
    /**
     * Inject Request object via class constructor
     */
    public function __construct(Request $request, $config = [])
    {

        parent::__construct();
    }

    /**
     * Inject Request object via class method
     *
     * @param $id the id parameter from route definition
     * @param $request The injected request object
     */
    public function index($id, Request $request)
    {

    }
}

```
