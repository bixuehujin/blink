开发与部署
=========

## 热加载

在开发阶段我们希望编写的代码能够实时生效, 马上看到效果, 这个时候我们需要热加载功能。Blink 可以通过如下两种方式达到热加载的效果:
 
1. 使用 `server:serve` 的 --live-reload 参数
 
 通过 --live-reload 参数, Blink 会调整 Swoole 的 maxRequests 设置, 每次处理请求后 Worker 立即退出, 该机制只能热加载应用层代码, 如果涉及
 框架的更新则需要重启服务。
 
2. 使用 `server:serve` 的 --cli 参数
 
 通过应用 --cli 参数, Blink 会运行在 PHP 的内置 Web 服务器上, 这时每次请求处理后, PHP 会释放所有资源, 达到完美的热加载。不过该机制下
 无法使用 Swoole 特性, 如果项目依赖 Swoole 的某些特性, 将不能使用该机制。
 
 
## 部署

Blink 支持两种部署方式, 部署在 Swoole 上或者 PHP-FPM 上, 开发者可以根据自己的实际情况选择部署的方式, 下面分别介绍:

**Swoole**

我们可以把 Blink 应用部署在 Swoole 上, 以便获得可观的性能提升。部署在 Swoole 上, 我们只需要使用 Blink 内置的服务管理命令就可以了:

```
./blink server:start  - 启动服务
./blink server:stop   - 停止服务
./blink server:restart - 重启服务
./blink server:reload  - reload服务, 重启所有 Worker
```

如果默认服务的配置不能满足应用的需求, 我们可以修改 `src/config/server.php` 文件, 自定义服务配置。


**PHP-FPM**

如果我们的应用对性能没有严格的要求, 也不需要依赖任何 Swoole 专有特性, Blink 也可以部署在 PHP-FPM 或者 Apache 之下, 该种部署方式对我们
的运维要求更低。

`web/index.php` 文件是该部署方式的入口文件, 如果我们把应用部署在 FPM 下, 只需要在 Nginx 配置文件中添加如下配置就可以了:

```
server {
        listen          80;
        #server_name rethinkphp.com;

        root /path/to/blink-seed/web;
        index index.php index.html;

        location / {
                try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
                try_files $uri =404;
                include fastcgi_params;
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
}

```
