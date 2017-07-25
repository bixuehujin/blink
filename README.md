Blink - A high performance web framework and application server in PHP
======================================================================

[![Build Status](https://travis-ci.org/bixuehujin/blink.svg?branch=master)](https://travis-ci.org/bixuehujin/blink)
[![Latest Stable Version](https://poser.pugx.org/blink/blink/v/stable)](https://packagist.org/packages/blink/blink)
[![Latest Unstable Version](https://poser.pugx.org/blink/blink/v/unstable)](https://packagist.org/packages/blink/blink)


Blink is a micro web framework for building long-running and high performance services, the design heavily inspired by Yii2
and Laravel. Blink aims to provide the most expressive and elegant API and try to make the experience of web development
as pleasant as possible.

Besides, Blink is also an application server that can serve requests directly in PHP, without php-fpm or Apache's mod_php.
we use the [Swoole extension](https://github.com/swoole/swoole-src) as the underlying networking library. This can easily make
our PHP application 100% faster in just a blink.

## Community

 * Gitter (Worldwide): [![Join the chat at https://gitter.im/bixuehujin/blink](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/bixuehujin/blink?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
 * QQ群 (China): 114632054

## Why build this?

In php-fpm or mod_php, all resources like objects and database connections only live within a single request,
all these resources will be freed once the request terminates. This works fine with simple applications without
much traffic, but for large scale applications, the performance impact of reallocating resources on every request
is really huge.

Because of this, we are always trying to find a way to reduce unnecessary resources reallocating on every request, and
I'm finally very glad to announce that **Blink** is exactly the answer!


## Features

* Powered by Swoole, serve requests in PHP directly
* Dependency Injection & Service Locator
* Routing for Restful APIs
* Authentication & Authorization abstraction
* Session Management abstraction
* Middleware support for Request and Response
* Plugins support
* PHPUnit integration for unit tests

## Installation

Install the latest version with

```bash
composer create-project --prefer-dist blink/seed your-app
```

## Documentation

Blink is fully documented in both English and Chinese:

 * [English Documentation](https://docs.rethinkphp.com/blink-framework/v0.3/en)
 * [简体中文文档](https://docs.rethinkphp.com/blink-framework/v0.3/zh-CN)

## Related Projects

 * [blink-redis](https://github.com/rethinkphp/blink-redis) A Redis component for the Blink Framework
 * [blink-sentry](https://github.com/bixuehujin/blink-sentry) The Sentry integration for Blink Framework.
 * [notejam_blink](https://github.com/Whyounes/notejam_blink) A notejam demo application for Blink.

## Participating

**Blink is still in active development, so your participation is very welcome!**

You may participate in the following ways:

* [Report issues or Start a design discussion](https://github.com/bixuehujin/blink/issues)
* Develop features or write documentation via [Pull Requests](https://github.com/bixuehujin/blink/pulls)

## Author

Follow me on [Twitter](https://twitter.com/bixuehujin) or [Weibo](http://weibo.com/bixuehujin) (Mainland China) to
receive news and updates about the framework.

## License

The Blink framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
