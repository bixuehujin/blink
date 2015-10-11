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

In the addition, the middleware architecture for Request and Response is already in the plan, we will provide more
custom mechanisms to transform the request or response data in a more flexible way.
