Development Tips 
================

This section we will introduce some useful utilities to help developers to work more efficiently.

## Live Reload

In the development process, it would be great to see how everything is going without a server restart, thus we need live
reloading support. In Blink, there are two ways to achieve live reloading:
 
1. Using the --live-reload option of the `server:serve` command
 
 By utilizing the --live-reload option, Blink will modify SwServer's `maxRequests` configuration automatically, once a request is
 handled, the worker will be stopped and a new worker will be created, just like PHP-FPM does. 
 
 But this kind of reload only works for application's code, if you upgraded the Blink Framework, a restart is still required.
 
2. Using the --cli option of the `server:serve` command
 
 By utilizing the --cli option, Blink will running upon the PHP's built-in web server. Once a request is handled, all resources
 are destroyed. But if your projects rely on some Swoole specific features, this mechanism can't be used.
 
 
## PsySH

Blink integrated with the interactive debugger [PsySH](https://psysh.org), we can interactive with Blink application through
PsySH by the following command:

```bash
./blink shell
```

For more detail information about PsySH, you can refer it's official documentation.
