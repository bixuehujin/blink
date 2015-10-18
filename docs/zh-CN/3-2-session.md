Session 管理
============

Session 允许用户在多个请求中共享数据，在传统 PHP 程序中，我们可以通过 `$_SESSION` 超全局变量来直接获取 Session 数据。但是在 Blink
应用中，`$_SESSION` 超全局变量是没有用也不能被使用的，我们必须通过 session 服务来获取 Sesson 数据。

另外，由 PHP 提供的 Session 相关函数也不能出现在 Blink 应用中，以避免出现一些未知的 Bug 或者未定义的行为。


Session 服务
-----------

Blink 实现了 `blink\session\Manager` 来应用提供 Session 服务的管理，在应用中，我们可以通过 `session()` 辅助方法来获取 Session 服务的
实例，下面是几个展示如何使用 Session 服务的例子：

```php
// 获取 Session 服务的实例
$manager = session();

// 创建一个新的 Session 对象并保存，返回对象为 \blink\session\Session 类的实例
$session = $manager->put($data);

// 获取新创建 Session 的 Session ID
$sessionId = $session->id;

// 通过 Session ID 获取 Session 数据
$session = $manager->get($sessionId);

// 通过 Session ID 写入新的 Session 数据
$manager->set($sessionId, $newData);

// 通过 Session ID 销毁 Session 数据
$manager->destroy($sessionId);

```

在上面的例子中， `put()` 和 `get()` 方法都返回一个 `blink\session\Session` 类的实例。`blink\session\Session` 对象是一个以*键值对*形式
存在的 Session 数据集合，并提供了一些有用的方法来管理 Session 数据。

在 Blink 中，我们也可以实现自定义的 Session 服务，唯一要做的就是实现 `blink\session\Contract` 接口并在配置文件中配置好该服务。


Session 存储
-----------

默认情况下，Blink 采用文件来存储 Session 数据。我们可以通过实现自定义的 Session 存储类来改变这个行为，实现自定义的存储类需要实现
`blink\session\StorageContract` 这个接口，更多可以参考 `blink\session\FileStorage` 类的实现。
