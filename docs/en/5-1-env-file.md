Environment Configuration
=========================

Starting from v0.4, Blink added dotenv alike mechanism to store environment specific or sensitive configurations.

To utilize this feature, we can create different env config file with different name, such as `env.dev` for development 
and `env.prod` for production. 

The following is the example:

1. env.dev

    ```
    env=dev
    mysql_host=localhost
    mysql_port=3306
    ```

2. env.prod

    ```
    env=prod
    mysql_host=2.1.3.4 # the ip address
    mysql_port=3306
    ```

And then, in our php configuration file, we utilize `env()` helper function to retrieve env config:

```php
// located in src/config/services.php
'mysql' => [
    'host' => env('mysql_host', 'localhost'),
    'port' => env('mysql_port', 3306),
]
```

At last, we specify an environment variable named `ENV_FILE` to start Blink application server:

```
ENV_FILE=env.dev ./blink server:serve
```

Now, the env config is working for us~

