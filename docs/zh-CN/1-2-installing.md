安装Blink
========

1. 安装 Swoole 扩展
------------------

安装前确保您的 PHP 版本大于 php 5.5，之后执行以下命令安装 Swoole:

```
$ pecl install swoole
```

然后执行命令 `php -m | grep swoole` 确保 Swoole 扩展加载成功。

2. 通过 Composer 安装 Blink
--------------------------

如果你没有安装 Composer， 你可以通过如下方式安装：

```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Composer 安装后，你可以通过 Composer 的 create-project 的命令创建项目并安装依赖：

```bash
composer create-project --prefer-dist blink/seed your-app
```

3. 确认安装成功
-------------

启动 Blink 确认安装成功：

```
cd /path/to/your-app
php blink server serve
```

成功之后， 打开浏览器在地址栏输入 http://localhost:7788/ , 如果浏览器显示 **"Hello world, Blink"** 的欢迎语那就表示 Blink
已经正常运行。
