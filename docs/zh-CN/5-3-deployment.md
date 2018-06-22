应用部署
=======

Blink 支持两种部署方式, 部署在 Swoole 上或者 PHP-FPM 上, 开发者可以根据自己的实际情况选择部署的方式, 下面分别介绍:

## Swoole

我们可以把 Blink 应用部署在 Swoole 上, 以便获得可观的性能提升。部署在 Swoole 上, 我们只需要使用 Blink 内置的服务管理命令就可以了:

```
./blink server:start  - 启动服务
./blink server:stop   - 停止服务
./blink server:restart - 重启服务
./blink server:reload  - reload服务, 重启所有 Worker
```

如果默认服务的配置不能满足应用的需求, 我们可以修改 `src/config/server.php` 文件, 自定义服务配置。

从 v0.4 开始，Blink 添加了服务管理工具支持，可以非常方便的帮助我们将 Blink 应用托管在 systemd 下，让 Blink 应用作为系统服务运行。

我们通过 `./blink server:install` 将 Blink 应用安装成系统服务，服务的名称由 `SwServer::$name` 决定，这个命令会做两件事请：

1. 为我们自动生成 systemd 相关的配置文件
2. 从 env.example 拷贝一份环境配置示例到 /etc/default 目录下, 文件名与服务名称相同

在修改好环境配置之后，就可以直接通过 systemd 配套命令进行服务管理。

## PHP-FPM

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
