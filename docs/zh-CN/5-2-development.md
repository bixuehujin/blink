开发配置
=======

本节介绍一些实用的工具和配置，帮助开发者在开发阶段更高效的工作。

## 热加载

在开发阶段我们希望编写的代码能够实时生效, 马上看到效果, 这个时候我们需要热加载功能。Blink 可以通过如下两种方式达到热加载的效果:
 
1. 使用 `server:serve` 的 --live-reload 参数
 
 通过 --live-reload 参数, Blink 会调整 Swoole 的 maxRequests 设置, 每次处理请求后 Worker 立即退出, 该机制只能热加载应用层代码, 如果涉及
 框架的更新则需要重启服务。
 
2. 使用 `server:serve` 的 --cli 参数
 
 通过应用 --cli 参数, Blink 会运行在 PHP 的内置 Web 服务器上, 这时每次请求处理后, PHP 会释放所有资源, 达到完美的热加载。不过该机制下
 无法使用 Swoole 特性, 如果项目依赖 Swoole 的某些特性, 将不能使用该机制。


## PsySH

Blink 集成了 [PsySH](https://psysh.org) 交互调试工具，可以很方便的与 Blink Application 进行交互。我们可以通过 `./blink shell` 的启动交互式环境。

关于 PsySH 的具体用法，可参见其官方文档。
