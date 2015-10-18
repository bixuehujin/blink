Error Handing and Logging
=========================

Logging
-------

Blink provides a PSR-3 compatible logging service that build upon Monolog logging library. Using this service, you can
easily log various types of messages into different targets, such as files, databases and emails.


In order to log your messages, you need configure the log service. In the following, this is the default configuration
provided by Blink's seed project:

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

In this example, we configured a file logging target that will write all messages to `stderr` if the message level
equals or less than *INFO*.

Besides the configuration, it is very convenient to access the log service and write logs, this is the example:

```php
// accessing the log service
$log = app('log');

// logging when system is unusable
$log->emergency('my message');

// logging when action must be taken immediately
$log->alert('my message');

// logging on critical conditions
$log->critical('my message');

// logging for runtime errors
$log->error('my message');

// logging for warnings
$log->warning('my message');

// logging for normal bug sighficant events
$log->notice('my message');

// logging for insteresting events
$log->info('my message');

// logging for detailed debug information
$log->debug('my message');
```

Error Handing
-------------

In Blink, all PHP errors are converted to `blink\core\ErrorException` exception automatically. By utilizing this feature,
it is possible to using `try ... catch` block to catch PHP errors.

Blink provides `errorHandler` service which implemented by `blink\core\ErrorHandler` class to handle exception and
errors, `errorHandler` will report exceptions or errors to `log` service by default, it is possible implement your
own errorHandler service to report errors in a different way such as Sentry.
