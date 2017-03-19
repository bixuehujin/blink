Plugins
=======

Blink 0.3 introduced the concept of plugins, with the light weight plugin support, it is possible to inject custom code
at the application bootstrapping phase, such as registering services, adding routes. With the support of plugins, we can
achieve the following goals:

1. Implement self contained modules, we can split a large project into several modules.
2. Better code reuse support, we can share similar functionality among different Blink projects.


## Writing a Plugin

In order to write a Blink Plugin, we should implement the `blink\core\PluginContract` interface and expose an `install()`
method. The method will be called with the `blink\core\Application` as its first argument at application bootstrapping phase.

In this phase, we can perform some plugin initialization tasks such as registering services and adding custom routes.

## Using a Plugin

Using a Plugin is just like using a Service in Blink, they following the same configuration convention. By default, we just 
need to add corresponding configuration in `src/config/plugins.php`, such as:

```
return [
    'plugin1' => [
        'class' => 'namespace/to/Plugin1Class',
        'prop1' => 'prop1',
    ],   
];
```

