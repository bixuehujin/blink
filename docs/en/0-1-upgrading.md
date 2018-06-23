# Upgrading to v0.4

If you are still using Blink v0.3 or previous versions, this section will guide you to upgrade to v0.4.

The following are the key points that should be noticed before upgrading:

** 1. The previous `Request::getBody()` is now renamed to `Request::getPayload()` **

According to PSR-7, The `Request::getBody()` will now returns a `Psr\Http\Message\StreamInterface` instance. The previous
feature provided by `getBody()` is now replaced by `getPayload()`. 

So, you just need to replace all `$request->getBody()` or `$reqeust->body` to `$request->getPayload()` or `$request->body`.
 

** 2. `Request::getParams()` and `Request::getPayload()` will not convert some special characters (such as `.`) into `_` **

If you rely on this feature, you should change your code. For more detail you may have a look at [this](https://stackoverflow.com/questions/68651/get-php-to-stop-replacing-characters-in-get-or-post-arrays)

