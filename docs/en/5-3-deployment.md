Deployment
==========

There are two ways to deploy Blink applications, deploy Blink on Swoole or PHP-FPM:

## Swoole

We can deploy Blink application on Swoole to gain impressive performance improvements, Blink provides several useful server
management commands to simplify our work:

```
./blink server:start  - Start server
./blink server:stop   - Stop server
./blink server:restart - Restart server
./blink server:reload  - Reload server, all workers will be restarted
```

If the default server configuration does not fit our need, we can modify the configuration in `src/config/server.php`.

Starting from v0.4, Blink added service management utility to help us to run Blink application under systemd as system
service.

We utilize `./blink server:install` to install Blink application as system service, the service name is controlled
by `SwServer::$name` which can be changed in `src/config/server.php`.

This command accomplish the following two things:

1. generate systemd related configuration files automatically
2. copy an example env config file (named `env.example`) to /etc/default directory with the same of the service

After the env file configured properly, we can manage the Blink application through systemd utilities.

## PHP-FPM

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
