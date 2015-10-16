Lifecycle
=========

As described in [About Blink](1-1-about-blink.md) section, Blink is not like traditional PHP framework that running
upon php-php or mod_php, so that the lifecycle of Blink application is also very different from the others. We should
always keep this in mind to avoid confusing with traditional PHP applications.

In php-fpm or mod_php, we all known that all resources like objects and database connections are only live within a
single request, all these resources will be freed once the request is terminated. This is works fine with simple
applications without much load, but for large scale applications, the performance impact of reallocating resources
on every request is really huge.

This is exactly the reason why we create Blink. In Blink, we are focused on reducing unnecessary resources reallocating
as much as possible. And exactly because of this, the lifecycle of Blink application is also much different, here is
the lifecycle of Blink application:


Boostrapping
------------

At first, Blink utilizes child PHP processes to handle user requests, every process lives with a Blink application,
and applications are created along with processes.

Once a child process created, a new application instance will be created in this process. After that, the
`$application->bootstrap()` method will be called and the following procedures will be take:

1. Setting applications configurations such as the default timezone.
2. Registering application services such as logger, errorHandler etc.
3. Registering application routes

Once the application bootstrapped successfully, the application will be ready to wait incoming requests.


Request Handing
---------------

After an application bootstrapped, it will wait the server and receive incoming requests. When a request comes to
the application, the `$application->handleRequest()` will be called, the following is the procedure how a request
is handled:

1. Dispatching the request through predefined routing
2. Creating corresponding controller instance
3. Invoking request middlewares
4. Executing corresponding controller action
5. Invoking response middlewares
6. Returning the response to server and terminate the request

A key difference in Blink is that Blink can handle multiple requests in a single application (ie. process), it reduced
unnecessary resources reallocating as much as possible. And also because of this, Blink application can run much
faster than php-fpm or mod_php.


Terminating
-----------

When an application (ie. the process) reached the `maxRequests` limit or received a `TERM` signal, the application will
terminate itself, any resources such as objects and database connections will be freed.
