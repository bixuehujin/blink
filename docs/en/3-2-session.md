Session Management
==================

Sessions allow data to be shared across multiple requests. In tranditional PHP application, you may access them
through global variable `$_SESSION` directly. but in Blink, it is not an option, the only way to access session
data is through session service.

Besides, session related function that provided by PHP should never be used in Blink application to avoid possible 
undefined behaviors.


Session Service
---------------

Blink implemented `blink\session\Manager` class to provide session service for our application, the session service can
be accessed through `session()` helper function, here is several example shows how session service can be used:

```php
$manager = session(); // retrieving the session service

$session = $manager->put($data); // creating a new session with $data, the returned $session is an instance of \blink\session\Session
$sessionId = $session->id; //accessing the session id

$session = $manager->get($sessionId); // gettting session data by session id

$manager->set($sessionId, $newData); // replacing session with new data

$manager->destroy($sessionId); // destroying session data by session id

```

In the example above, both `put()` and `get()` method return a `blink\session\Session` object. The `blink\session\Session`
is a collection of key-value pairs of sessions which provides some very helpful method to edit or update session.

In Blink, it is possible to implement your own custom session service, the only thing you need is implement the
`blink\session\Contract` interface and configure your service in configuration file.


Session Storage
---------------

By default, files are be used to storage session data in Blink, it is possible to change this behavior by using custom
session storage class. To implement our own session storage class, we need implement the `blink\session\StorageContract`
interface. For more detailed information, you can refer the implementation of `blink\session\FileStorage` class.
