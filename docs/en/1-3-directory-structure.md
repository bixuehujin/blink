Blink Directory Structure
=========================

Blink shipped with an recommended application structure that would be suit for various applications by default, 
The following is a short description for each individual directory:

```
your-app/                   application root directory
    composer.json           Composer configuration file, stored information about installed packages
    src/                    application source code
        config/
            app.php         basic application configurations
            server.php      Swoole server configuration
            services.php    service configurations
        console/            console commands
        http/               http related
            controllers/    controllers stored here
            routes.php      routing configurations
        models/             database models
        bootstrap.php       application bootstrap file
    tests/                  unit tests or functional tests for PHPUnit
    runtime/                runtime data or cache
    vendor/                 all vendor packages installed by Composer
    blink                   Blink command line entry script
```

And, of course, Blink is designed as flexible as enough, you absolutely can custom your application structure to suit 
your specific requirements.
