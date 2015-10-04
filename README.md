Blink - A web framework build for high performance services
===========================================================

[![Build Status](https://travis-ci.org/bixuehujin/blink.svg?branch=master)](https://travis-ci.org/bixuehujin/blink)

Blink is micro web framework with expressive, elegant APIs, which inspired much by Yii2 and Laravel. And the most
importantly, Blink is designed for long running services. Unlike other frameworks, Blink does't requires php-fpm or
mod_php to run PHP code, we currently taking [Swoole](https://github.com/swoole/swoole-src) extension to serve
requests in plain PHP.

## Features

* Powered by Swoole, serve requests in plain PHP
* Dependency Injection & Service Locator
* Routing for Restful APIs
* Authentication & Authorization abstraction
* Session Management abstraction
* PHPUnit integration for unit tests

## Installation

Install the latest version with

```bash
composer create-project --prefer-dist -s dev blink/seed your-app
```

## Documentation

 * [English Documentation](docs/en/README.md)
 * [简体中文文档](docs/zh-CN/README.md)

## Participating

**Blink is still in active development, your participating is very wellcome!**

You may participate in the following ways:

* [Report issues or Start a design discussion](https://github.com/bixuehujin/blink/issues)
* Develop features or write documentation via [Pull Requests](https://github.com/bixuehujin/blink/pulls)

