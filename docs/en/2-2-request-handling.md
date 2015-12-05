Request Handling
================

Accessing The Request
---------------------

In Blink, you can obtain an instance of the current HTTP request via dependency injection by type-hint, and then 
the current request instance will automatically be injected by the service locator.

The following example shows how to inject the request object and access its query parameter and request body:

```php
use \bink\core\Object;
use \bink\http\Request;

class Controller extends Object
{
    public function index(Request $request)
    {
        $type = $request->params->get('type'); // accessing the `type` query parameter
        $params = $request->params->all(); // accessing all query parameters

        $name = $request->body->get('name'); // accessing request body by key
        $body = $request->body->all(); // accessing all request body
    }
}
```

Please check out the [Implementation](/src/http/Request.php) of `\blink\http\Request` to find out more useful method to
access request variables.


Returning Data
--------------

In Blink, it is very convenient to send data back to client by returning data directly in controller method. The
returned data support both string and array, if an array is returned, Blink will json encode the data automatically
before sending back to client.

Here is the example:


```php
use \bink\core\Object;
use \bink\http\Request;

class Controller extends Object
{

    public function action1()
    {
        return 'this is a string'; // returns a string directly, the string will be sent to client without change.
    }

    public function action2()
    {
        return [
            'name' => 'foo'        // returns the data as array, the data will be json encoded automatically.
        ]
    }
}
```

Middleware
----------

Blink provides the *middleware* feature for both Request and Response, by utiling the middleware, we can
implement functionalities such as authentication or data formating very conveniently.

To use the middleware feature, we can just set the `middleware` property for Request or Response object.
The following is an example that apply `BasicAccess` middleware for all requests:

```php
    'request' => [
        'class' => \blink\http\Request::class,
        'middleware' => [
            blink\auth\middleware\BasicAccess::class,
        ],
    ],
```

Besides, we can also specify our middlewares dynamically through `middleware()` method before the middleware
getting executed:

```php
 response()->middleware(Cors::class, true);
```

Here we prepend a middleware called `Cors` for Resposne, by using dynamically middleware, it is possible
to apply middleware only for specified controller or actions.


Implementing a Middleware
-------------------------

In Blink, you can implement a middleware by implementing the `blink\core\MiddlewareContract` interface, to
implement the interface, you need implement the `handle()` method, here is the prototype:


```php
public function handle($owner)
{

}
```

The `$owner` parameter is the Request or Response object that the middleware applied with.

Besides, Blink provdes a `blink\auth\middleware\BasicAccess` middleware for the Http BasicAuth authentication,
you can also refer this for how to implement a middleware.
