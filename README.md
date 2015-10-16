Blink - A web framework build for high performance services
===========================================================

[![Build Status](https://travis-ci.org/bixuehujin/blink.svg?branch=master)](https://travis-ci.org/bixuehujin/blink)
[![Latest Stable Version](https://poser.pugx.org/blink/blink/v/stable)](https://packagist.org/packages/blink/blink)
[![Latest Unstable Version](https://poser.pugx.org/blink/blink/v/unstable)](https://packagist.org/packages/blink/blink)


Blink is micro web framework build for long-running and high performance services, the design heavily inspired by Yii2
and Laravel. Blink aims to provide the most expressive and elegant API and try to make the experience of web development
as pleasant as possible.

Besides, Blink is not like other frameworks that running upon php-fpm or apache's mod_php, we currently taking
[Swoole extension](https://github.com/swoole/swoole-src) to serve requests directly in PHP, this can easily make
our PHP application 100% faster just in a blink.

## Why Built this?

In php-fpm or mod_php, all resources like objects and database connections are only live within a single request,
all these resources will be freed once the request terminated. This is works fine with simple applications without
much traffic, but for large scale applications, the performance impact of reallocating resources on every request
is really huge.

As this reason, we are always trying to find a way to reduce unnecessary resources reallocating on every request, and
i'm finally very glad to announce that **Blink** is exactly the answer!


## Features

* Powered by Swoole, serve requests in PHP directly
* Dependency Injection & Service Locator
* Routing for Restful APIs
* Authentication & Authorization abstraction
* Session Management abstraction
* Middleware support for Request and Response
* PHPUnit integration for unit tests

## Installation

Install the latest version with

```bash
composer create-project --prefer-dist -s dev blink/seed your-app
```

## Documentation

Blink is fully documented in both English and Chinese:

 * [English Documentation](docs/en/README.md)
 * [简体中文文档](docs/zh-CN/README.md)


## Participating

**Blink is still in active development, your participating is very wellcome!**

You may participate in the following ways:

* [Report issues or Start a design discussion](https://github.com/bixuehujin/blink/issues)
* Develop features or write documentation via [Pull Requests](https://github.com/bixuehujin/blink/pulls)

## Author

Follow me on [Twitter](https://twitter.com/bixuehujin) or [Weibo](http://weibo.com/bixuehujin) (Mainland China) to
receive news and updates about the framework.

## License

The Blink framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
