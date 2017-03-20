Development and Deployment
==========================

## Live Reload

In the development process, it would be great to see how everything is going without a server restart, thus we need live
reloading support. In Blink, there are two ways to achieve live reloading:
 
1. Using the --live-reload option of the `server:serve` command
 
 By utilizing the --live-reload option, Blink will modify SwServer's `maxRequests` configuration automatically, once a request is
 handled, the worker will be stopped and a new worker will be created, just like PHP-FPM does. 
 
 But this kind of reload only works for application's code, if you upgrade the Blink Framework, a restart is still required.
 
2. Using the --cli option of the `server:serve` command
 
 By utilizing the --cli option, Blink will running upon the PHP's built-in web server. Once a request is handled, all resources
 are destroyed. But if your projects rely on some Swoole specific features, this mechanism can't be used.
 
 
## Deployment

There are two ways to deploy Blink applications, deploy Blink on Swoole or PHP-FPM:

**Swoole**

We can deploy Blink application on Swoole to gain impressive performance improvements, Blink provides several useful server
management commands to simplify our work:

```
./blink server:start  - Start server
./blink server:stop   - Stop server
./blink server:restart - Restart server
./blink server:reload  - Reload server, all workers will be restarted
```

If the default server configuration does not fit our need, we can modify the configuration in `src/config/server.php`.


**PHP-FPM**

If no Swoole specific feature is used, Blink can also be deployed under PHP-FPM or Apache just like other PHP frameworks.

`web/index.php` is the entry file for PHP-FPM or Apache. The following is configuration for Nginx that works for Blink.

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
