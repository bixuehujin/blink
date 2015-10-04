Blink 目录结构
=============

Blink 默认应用模板提供一套满足绝大部分应用场景的目录结构，其各部分功能及介绍如下：

```
your-app/                   应用根目录
    composer.json           Composer 配置, 描述应用依赖软件包的信息
    src/                    应用源代码
        config/
            app.php         应用基本配置
            server.php      Swoole 服务器配置
            services.php    应用服务配置
        console/            控制台命令相关
        http/               Http 相关
            controllers/    控制器文件夹
            routes.php      路由配置
        models/             数据库模型
        bootstrap.php   
    tests/                  应用单元测试或功能测试
    runtime/                应用运行时临时数据，如日志
    vendor/                 所有 Composer 安装的软件包
    blink                   Blink 命令行脚本入后
```

当然，Blink 提供足够灵活的自定义功能，如果你觉得该目录结构并不满足你的需求，你完全可以配置出任何你需要的目录结构。
