日志与错误处理
===========

日志
---

Blink 提供了一个构建在 Monolog 日志库之上、兼容 PSR-3 日志标准的日志服务组件。通过日志服务，我们可以轻松的把各种类型的消息记录到诸如文件、
数据库、邮件等媒介中。

为了记录日志消息，我们首先需要配置日志服务，下面是 Blink 的 seed 项目提供的默认日志配置：

```php
'log' => [
    'class' => 'blink\log\Logger',
    'targets' => [
        'file' => [
            'class' => 'blink\log\StreamTarget',
            'enabled' => true,
            'stream' => 'php://stderr',
            'level' => 'info',
        ]
    ],
],
```

在这个例子中，我们定义了一个叫做 `file` 的媒介，目的是将所有消息级别小于或等于 *INFO* 的消息写入到 `stderr`中：

另外，获取日志服务和写日志也是很方便的，实例如下：

```php
// 获取日志服务的实例
$log = app('log');

// emergency 日志类型，系统不可用
$log->emergency('my message');

// alert 日志类型，必要的措施必须马上采取
$log->alert('my message');

// critical 日志类型，危险条件触发
$log->critical('my message');

// error 日志类型，运行时错误
$log->error('my message');

// warning 日志类型，警告
$log->warning('my message');

// notice 日志类型，通常且值得注意的事件
$log->notice('my message');

// info 日志类型，记录感兴趣的事件
$log->info('my message');

// debug 日志类型，记录详细的调试信息
$log->debug('my message');
```

错误处理
-------

Blink 中，所有的 PHP 错误都会自动转换成 `blink\core\ErrorException` 异常，通过这个特性，我们可以用 try ... catch 来捕获 PHP 错误。

Blink 提供由 `blink\core\ErrorHandler` 类实现的 `errorHandler` 服务来处理 PHP 异常。默认情况下，`errorHandler` 会把所有的异常上报给
`log` 服务，我们也可以实现自己的 `errorHandler`，采用不同的方式来处理这些异常，比如上报给 Sentry。
