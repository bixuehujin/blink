属性和配置
========

属性和配置的设计借鉴于 Yii 框架，Blink 实现的 Yii 框架中该特性的子集，如果您对 Yii 的这套理念熟悉，本节只需简单看看即可。如果不熟，您也可以参考
Yii 的[相关文档](https://github.com/yiisoft/yii2/blob/master/docs/guide/concept-configurations.md) 获得更详细的信息。


属性
----

Blink 利用 PHP 的魔术方法实现了增强版的对象属性，通过增强版的属性实现，我们可以在读或者写属性的时候执行一些自定义的代码。Blink 通过
`blink\core\Object` 这个类来提供这一特性，它通过定义类的 *getter* and *setter* 方法来定义这种属性，如果一个类需要这种功能，我们只需要
继承 `blink\core\Object` 类或者他的子类即可。

在下面的实例中，我们通过定义 getLabel() 和 setLabel() 两个方法定义了 label 这个属性。相比 PHP 原生提供的属性，该属性可以在设置它的值时
自动调用 trim 函数，实现自定义代码注入：


```php
use blink\core\Object;

class Foo extends Object
{
    private $_label;

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = trim($value);
    }
}
```

我们也可以只定义 getter 方法而不定义 setter 方法，这样的属性叫做 read only 属性，如果对该类属性赋值会触发 `blink\core\InvalidCallException`
异常；同样的，只定义 setter 方法而不定义 getter 方法的属性叫做 write only 属性，如果尝试读取该类属性值也会触发异常。


除了继承 `blink\core\Object` 这个类，Blink 也提供 `blink\core\ObjectTrait` 和 `blink\core\Configure` 接口，通过使用他们，
我们可以很容易的让第三方库的代码与 Blink 兼容，使用 Blink 提供的 属性和配置 的特性。


配置
----

配置在 Blink 中广泛应用于对象创建和初始化已有对象。一个配置通常包含待创建对象的类名和一系列用于初始化该对象属性的值。下面是一个采用配置创建和
初始化 log 服务的例子：


```php
$config = [
    'class' => 'blink\session\Manager',
    'expires' => 3600 * 24 * 15,
    'storage' => [
        'class' => 'blink\session\FileStorage',
        'path' => 'path/to/sessions'
    ]
];
$session = make($config);
```

`make()` 函数是一个采用配置快速创建对象的辅助函数，他先根据配置里包含的类名创建对象，然后再初始化对象的其他属性。


配置格式
-------

一个配置的格式如下：

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    // 更多的属性
]
```

其中:

* class 元素指定待创建对象的类名
* propertyName 元素用于初始化对象对应的属性，key 是属性名称，value 是属性的初始值。注意，只有公开的成员变量和通过 getter 和 setter 定义的属性可以被配置。
