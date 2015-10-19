Installing Blink
================

1. Install Swoole Extension
---------------------------


Before your installation please be sure your PHP version is greater then 5.5, then run the following command
to install Swoole:

```
$ pecl install swoole
```

Then, run `php -m | grep swoole` command to make sure `Swoole` installed successfully.

2. Install Blink Framework via Composer
---------------------------------------

If you still do not have a Composer installed, you can install it by:

```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

After Composer installed, you are able to create a Blink project and install all its dependencies by using Composer's 
`create-project` command:

```bash
composer create-project --prefer-dist blink/seed your-app
```

3. Confirm The Installation
---------------------------

Start Blink server to ensure the installation:

```
cd /path/to/your-app
php blink server serve
```

Then, launch your browser and open http://localhost:7788/ , if you can see a welcome message saying
**"Hello world, Blink"** that would indicates Blink is already installed successfully.
