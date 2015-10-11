Session Management
==================

Sessions allow data to be persisted across multiple requests. In tranditional PHP application, you may access them
through global variable `$_SESSION` directly. but in Blink, it is not an option, the only way to access session
data is through session service.

Besides, session related function that provided by PHP should never be used in Blink application to avoid possible 
undefined behaviors.


Session Service
---------------

Blink provides session storage function by the session application service, it can be accessed through `session()` helper
function, here is several example shows how session service can be used:

```php
$session = session(); // retrieving the session service

$sessionId = $session->put($data); // creating a new session with $data

$data = $session->get($sessionId); // gettting session data by session id

$session->set($sessionId, $newData); // replacing session with new data

$session->destroy($sessionId); // destroying session data by session id

```

In the addition, Blink provides `blink\session\SessionBag` class as a session container that makes session management
much easier.

In Blink, it is possible to implement your own custom session service, the only thing you need is implement the
`blink\session\Contract` interface and configure your service in configuration file.


Session Storage
---------------

By default, files are be used to storage session data in Blink, it is possible to change this by using custom
session storage class. For how to implement your own session storage class, please refer the implementation of
`blink\session\FileStorage` class.
