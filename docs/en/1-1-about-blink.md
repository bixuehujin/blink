What is Blink?
==============

Blink is a web framework aims to build long-running and high performance services by providing
expressive and elegant APIs. It is designed to be extensible, it is should very simple to extend the
functionality of Blink. In the addition, Blink try its best to reduce common tasks by providing
common used components such as Routing, Authentication, Dependency Injection and Logging etc.


Compared to Other Frameworks
----------------------------

The main difference that Blink vary from other traditional PHP frameworks is that Blink application
doesn't require any extra web server (php-fpm on Nginx or mod_php on Apache) to execute our code. Instead,
Blink shipped its own server which implemented by [Swoole Extension](https://github.com/swoole/swoole-src) to serve
user's requests in plain PHP.

There are server advantages for Blink that implementing server in PHP but Nginx or Apache:

* Reduced the overhead of PHP's Request Startup - Request Shutdown lifecycle, many objects do not need to be recreated on
  every request.
* The server can gain more control on computer resources, it is possible to implement something that php-fpm or mod_php can't.

In addition, we believe there are much more imaginations that can be achieved by this.


What cases Blink best suits for?
------------------------------------

As Blink is designed to be a generic web framework, so that Blink is undoubtedly capability of any tasks that other framework
can, but due to it's unique architecture, Blink extremely suits for the following cases:

* Cases that have more strict performance requirements, significant performance improvements is possible by using Blink.
* Implement features that may limited by php-fpm or mod_php, such as realtime chatting.


Requirements and Prerequisites
------------------------------

* PHP 5.5 and above
* Swoole Extension 1.7.19 and above
