Blink是什么
==========

Blink 是一个为构建 “long running” 服务而生的 Web 微型高性能框架，它为构建 Web 应用程序提供简洁优雅的API，尽量的减轻我们的常规开发工作。
与此同时，Blink尽可能的保持设计的简洁与可扩展性，允许开发者更加灵活自如的使用。Blink 提供了常用诸如路由、登陆认证、依赖注入、日志处理
等核心组件，让开发者专注于应用本身。


Blink与其他框架的比较
-------------------

Blink 与传统 PHP 的 Web 框架非常不同，Blink 的运行不需要 Web 服务器（php-fpm 之于 Nginx， mod_php 之于 Apache）。Blink 本身
就能充当 Web 服务器，直接处理来自客户端的请求。目前我们采用 [Swoole扩展](https://github.com/swoole/swoole-src) 作为底层服务支持。

众所周知，传统的 PHP 应用程序有 Request Startup 和 Request Shutdown 的生命周期，所有的对象在请求后都将销毁，而 Blink 于此不同，
Blink 许多对象都能留存与多个请求之间，减少对象反复创建销毁的性能损失。

当然，Blink的潜力不止于此，我们可以发挥更多的想象空间，实现其他框架不能想象或者很难实现的功能。


Blink适用场景
------------

* 对性能有更加严格要求的场景，通过 Blink 可以获得可观的性能提升
* 实现传统框架因 php-fpm 或 mod_php 的限制而难以实现的功能，如实时聊天


环境要求
-------

* PHP 5.5 以上版本
* Swoole 扩展 1.7.19 以上版本
