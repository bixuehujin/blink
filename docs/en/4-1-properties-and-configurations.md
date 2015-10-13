Properties and Configurations
=============================

The design of Properties and Configurations is inspired by Yii Framework, Blink implemented a subset of Yii's Properties
and Configurations feature, you can checkout [Yii's corresponding documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/concept-configurations.md)
for more detailed information.

Properties
----------

Blink enhanced the property implementation by utilizing PHP's magic method. With the enhanced property, is it possible
to inject some custom code when reading or writing properties. Blink provides the feature by `blink\core\Object` class,
it implemented the enhanced property by defining *getter* and *setter* class method. If a class need this functionality,
it should extend from `blink\core\Object` or its child class.

A getter method is a method whose name starts with the word get; a setter method starts with set. The name after the 
get or set prefix defines the name of a property. For example, a getter getLabel() and/or a setter setLabel() defines a
property named label, as shown in the following code:

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

A property defined by a getter without a setter is read only. Trying to assign a value to such a property will cause an 
`blink\core\InvalidCallException`. Similarly, a property defined by a setter without a getter is write only, and trying
to read such a property will also cause an exception. 

Besides the class `blink\core\Object`, Blink also provides `blink\core\ObjectTrait` trait and `blink\core\Configure`
interface, by using them, it is useful to making third-party classes compatible with Blink and utilizing the Properties
and Configurations feature of Blink.


Configurations
--------------

Configurations are widely used in Blink when creating new objects or initializing existing objects. Configurations
usually include the class name of the object being created, and a list of initial values that should be assigned to
the object's properties.

In the following, it is a configuration used to create and initialize a log service:

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

The `make()` function takes a configuration array as its argument, and creates an object by instantiating the class
name in the configuration. When the object is instantiated, the rest of the configuration will be used to initialize
the object's properties.

Configuration Format
--------------------

The format of a configuration can be formally described as:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
]
```

where:

* The class element specifies a fully qualified class name for the object being created.
* The propertyName elements specify the initial values for the named property. The keys are the property names, and
  the values are the corresponding initial values. Only public member variables and properties defined by getters/setters
  can be configured.
