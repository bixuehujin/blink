# What is Blink?


Blink is a web framework, designed to build long-running and high-performance services, providing
expressive and elegant APIs. It is very simple to extend the
functionality of Blink. Additionally, Blink provides commonly used components out of the box, including Routing, Authentication, Dependency Injection and Logging, just to name a few.


## What Makes It Special


 What makes Blink special comparing to traditional PHP frameworks is that a Blink application
doesn't require a third-party web server (php-fpm on Nginx or mod_php on Apache). Instead,
It has its own server, which is implemented by [Swoole Extension](https://github.com/swoole/swoole-src) to serve
user's requests in plain PHP.

Blink's built-in web server has several advantages over the existing web servers namely Nginx or Apache:

* Reduced the overhead of PHP's Request Startup - Request Shutdown lifecycle (many objects do not need to be recreated on
  every request)
* The server can gain more control on computer resources, it is possible to implement something that php-fpm or mod_php can't.


## What use cases Blink best suits for?


Since Blink is designed to be a generic framework, it is capable of supporting all the basic tasks other frameworks can do, but due to its unique architecture, It will be extremely promising in the following use cases:

* Cases having more strict performance requirements.
* Implement features that may limit by php-fpm or mod_php, such as real-time chatting.


## Requirements and Prerequisites


* PHP 5.5 and above
* Swoole Extension 1.7.19 and above