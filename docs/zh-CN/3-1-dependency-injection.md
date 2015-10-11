依赖注入与服务定位器
================

依赖注入是控制反转（Inversion of Control，缩写 IoC）的一种实现方法，是面向对象编程的一种设计原则，通过依赖注入，
可以降低类与类之间的耦合，让代码的调试和测试都变得更加简单。

依赖注入
-------

Blink 通过 `blink\di\Container` 提供 DI 容器功能，它支持构造方法注入、Setter属性注入和回调注入三种类型的注入方式。


**构造方法注入**

在参数类型提示的帮助下，DI 容器实现了构造方法注入。当容器被用于创建一个新对象时，类型提示会告诉它要依赖什么类或接口。
容器会尝试获取它所依赖的类或接口的实例，然后通过构造器将其注入新的对象。例如：

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// 上面的代码等价于：
$bar = new Bar;
$foo = new Foo($bar);
```

**Setter 和属性注入**

Setter 和属性注入是通过对象配置提供支持的。当注册一个依赖或创建一个新对象时，你可以提供一个配置，该配置会提供给容器用于通过相应的
Setter 或属性注入依赖。例如：

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

**PHP 回调注入**

这种情况下，容器将使用一个注册过的 PHP 回调创建一个类的新实例。回调负责解决依赖并将其恰当地注入新创建的对象。例如：

```php
$container->set('Foo', function () {
    return new Foo(new Bar);
});

$foo = $container->get('Foo');
```


服务定位器
---------

服务定位器是一个知道如何提供各种应用所需的服务的对象。在服务定位器中，每个服务都只有一个单独的实例（服务都是单例的），
并通过 ID 唯一地标识。用这个 ID 就能从服务定位器中得到这个组件。

Blink 中 `blink\core\ServiceLocator` 实现了服务定位器模式，通过服务定位器，我们可以很容易的配置这些服务，
并且每个服务的实现都是可以替换的，只要它们实现的相同的接口。在 Blink 中，整个 application 其实就是一个服务定位器，
它上面挂载了应用所需要的全部服务，诸如errorHandler服务，日志服务、auth服务等等。

要使用服务定位器，第一步是要注册相关服务。服务可以通过 `blink\core\ServiceLocator::bind()` 方法进行注册。
以下的方法展示了注册组件的不同方法：

```php
use blink\core\ServiceLocator;

$locator = new ServiceLocator;

// 1. 通过组件类名
$locator->bind('log', 'blink\log\Logger');

// 2. 通过配置数组
$locator->bind('log', [
    'class' => 'blink\log\Logger',
    'targets' => [],
]);

// 3. 通过匿名函数
$locator->bind('log', function () {
    return new blink\log\Logger([]);
});

// 4. 直接使用类的实例
$locator->bind('log', new blink\log\Logger());

```

一旦服务注册成功，你可以任选以下两种方式之一，通过它的 ID 访问它：

```php
$log = $locator->get('log');

// or

$log = $locator->log;
```

辅助函数
-------

** 创建对象 **

Blink 提供了构建与 DI 之上的辅助函数 `make($type, $params = [])`，用于快速创建类的实例并进行依赖注入，
通过 make 函数，可以方便的通过对象配置、类名创建实例，如：

```php

$object = make([
    'class' => 'blink\log\Logger',
    'prop1' => 'prop2',
]);

// 和

$object = make('blink\log\Logger');

```

** 获取服务实例 **

Blink 提供 `app()` 方法快速获取服务实例，如下：

```php
$log = app('log');

// 等价与

$log = app()->get('log');

```
