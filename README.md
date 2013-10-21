Storia
======

### Development Stacks
- PHP SlimFramework
- PHP >= 5.3.4
- PHP Composer Package Manager
- MongoDB Driver for PHP
- Apache


### Local Environment Setup Guide
- cd /var/www/
- mkdir storia
- git init
- git remote add origin git@github.com:magnificogeo/storia.git
- git pull
- git checkout server

- enjoy

- then go http://localhost/storia/server
- you'll see your index.php initialized at the default route "/"

### Start up local server and apache stuff

- sudo service apache2 (you can see various options here, restart, reload, start, stop etc..)
- cd /etc/apache2 (file directory for apache settings)
- cd /etc/apache2/sites-available (sites available and virtual host settings here)
- sudo a2ensite/a2dissite/a2enmod (enable and disable site, enable and disable apache modules)
