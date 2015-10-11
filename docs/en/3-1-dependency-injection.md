Dependency Injection and Service Locator
========================================

Dependency Injection as known as DI, is an implementation for the Inversion of Control (aka. IoC), a design
principle for object-oriented programming. By using DI, classes can be highly decoupled, making the debug and
testing life easier.


Dependency Injection
--------------------

Blink provides DI container feature through the the class `blink\di\Container`, it supports the following kinds of
dependency injection:

* Constructor injection
* Setter and property injection
* PHP callable injection
* Class method injection

**Constructor Injection**

The DI container supports constructor injection with the help of type hits for constructor parameters, the type hits
tell the container which classes or interfaces are dependent when it is used to created a new object. The container
will try to get the instance of the dependent classes or interfaces and then inject them into the new object through
constructor. For example:

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// which is equivalent to the following:
$bar = new Bar;
$foo = new Foo($bar);
```

**Setter and Property injection**

Setter and property injection is supported through object configurations. When registering a dependency or when creating
a new object, you can provide a configuration which will be used by the container to inject the dependencies through
the corresponding setters or properties. For example:

```php
use blink\core\Object;

class Foo extends Object
{
    public $bar;

    private $_qux;

    public function getQux()
    {
        return $this->_qux;
    }

    public function setQux(Qux $qux)
    {
        $this->_qux = $qux;
    }
}

$container->get('Foo', [], [
    'bar' => $container->get('Bar'),
    'qux' => $container->get('Qux'),
]);

```

**PHP Callable Injection**

In this case, the container will use a registered PHP callable to build new instances of a class. Each time when
`blink\di\Container::get()` is called, the corresponding callable will be invoked. The callable is responsible to
resolve the dependencies and inject them appropriately to the newly created objects. For example:

```php
$container->set('Foo', function () {
    return new Foo(new Bar);
});

$foo = $container->get('Foo');
```

**Class method injection**

Class method injection is a special type of DI where dependencies are declared using the type hits of method signature
and resolved in the runtime when the method is actually called. The main use case in Blink is in controller actions.
In this case, it is useful to keeping the MVC controller slim and light-weighted since it doesn't requires you to configure
all the possible dependencies of the controller beforehand.

```php
use blink\http\Request;

public function actionLogin($email, Request $request)
{
    $request->input('foo');
}
```


Service Locator
---------------

Service locator is an object that knows how to provide all sorts of services that an application might need. Within the
service locator, each service exists as only a single instance and uniquely identified by an ID. You can use the ID to
retrieve a service instance from the service locator.

In Blink, a service locator is simply an instance of `blink\core\ServiceLocator` or a child class. 

The most commonly used service locator is the Blink application object, which can be accessed through `app()` helper 
function. The services it provides are called application services, such as the `errorHandler`, `logging`, and `auth`. 

To use a service locator, the first step is to register services with it, a service can be registered via
`blink\core\ServiceLocator::bind()`, the following code shows different ways of registering services:


```php
use blink\core\ServiceLocator;

$locator = new ServiceLocator;

// 1. register "log" service using a class name
$locator->bind('log', 'blink\log\Logger');

// 2. register "log" service using a configuration array
$locator->bind('log', [
    'class' => 'blink\log\Logger',
    'targets' => [],
]);

// 3. register "log" using an anonymous function
$locator->bind('log', function () {
    return new blink\log\Logger([]);
});

// 4. resgister "log" using an object directly
$locator->bind('log', new blink\log\Logger());

```

Once a service has been registered, you can access ti using its ID, in one of the two following ways:

```php
$log = $locator->get('log');

// or

$log = $locator->log;
```

Helper Functions
----------------

** Creating Instances **

Blink provides a helper function `make($type, $params = [])` which build upon DI to create class instances and implement
dependency injection very conveniently. Here is the example:


```php

$object = make([
    'class' => 'blink\log\Logger',
    'prop1' => 'prop2',
]);

// and

$object = make('blink\log\Logger');

```

** Retrieving Service Instances **

Besides accessing Blink application object, we can also using `app()` function to access service instances by ID,
for example:

```php
$log = app('log');

// which is enquivalent to

$log = app()->get('log');

```
