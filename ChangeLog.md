Blink Framework Change Log
==========================

0.3.1 July 16, 2017
--------------------

- New: Added CookieAuthenticator for cookie based session handling
- New: Added blink\session\Manager::$sessionClass configuration to subclass Session class
- Enh: Supressed warning triggered by cli_set_process_title on macOS
- Enh: Added $asArray parameter to RequestActor::asJson() to control json decoding 
- Enh: Improved Auth::who() to accept Session object as argument
- Bug: Fixed routes lazy loading not working
- Chg: Deprecated Request::$sessionKey configuration



0.3.0 March 19, 2017
--------------------

- New: Added application plugins support
- New: Added grouped routes support
- New: Added RequestActor for easier functional testing cases
- New: Integrated PsySH for better debug experience
- New: Added CookieBag::all() to return all cookies
- New: Added the new server management commands(server:start server:stop server:restart server:serve server:reload)
- Enh: Better PHP7 exception support
- Enh: Added Custom PID file path support
- Enh: Improved automatically handling of Content-Type
- Enh: Added SwServer::$outputBufferSize parameter
- Enh: Improved Request::secure() to handle X-Forwarded-* headers
- End: Improved live reload support
- Chg: Removed php 5.5 support
- Bug: Fixed "Expected array for frame 0" error for PHP7+Xdebug



0.2.1 February 03, 2016
-----------------------

- Enh: Improved blink\session\Manager::get() with empty value
- Enh: Improved support for PHP7
- New: Added SwServer::$maxPackageLength configuration



0.2.0 December 05, 2015
-----------------------

- Bug #10: Fixed get body value with blink\http\Request::input()
- Enh: Added blink\core\Application::currentRequest property
- Enh: Added automatically session directory creation support
- New: Added file uploading support
- New: Added CgiServer for php-fpm or Apache's mod_php support
- New: Added logger() helper function to get log service
- New: Added file uploading support
- New: Added Cookie handling support
- New: Added blink\http\Request::redirect() method

0.1.0 October 19, 2015
---------------------

- The first release.
