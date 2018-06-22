环境配置
=======

从 v0.4 开始，Blink 支持类似 dotenv 的环境变量配置机制，通过该机制，我们可以很方便的将环境相关或者敏感信息写入环境配置。

比如我们可以针对开发环境和生产环境创建不同的配置文件，下面是一个简单的例子：

1. env.dev

    ```
    env=dev
    mysql_host=localhost
    mysql_port=3306
    ```

2. env.prod

    ```
    env=prod
    mysql_host=2.1.3.4 # the ip address
    mysql_port=3306
    ```

然后，在配置文件或者程序中，通过使用 `env()` 函数获取环境配置，类似如下：

```php
'mysql' => [
    'host' => env('mysql_host', 'localhost'),
    'port' => env('mysql_port', 3306),
]
```

最后，在启动应用服务器的时候通过 `ENV_FILE` 环境变量指定需要加载的环境配置：

```
ENV_FILE=env.dev ./blink server:serve
```

这样，就能使用 Blink 提供的环境配置机制了。
