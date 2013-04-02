# README #

This is a Symfony2 application that serves a RESTful API for managing multimedia with the Ayamel Resource Library.

This file contains installation instructions, and a brief explanation of how the project is structured.

## Installation ##

Brief instructions for both setting up a new server, and setting up the application.

### System Dependencies ###

These are the packages I installed on a clean Ubuntu 10.04 dev machine for testing, not all of them are actually being used at the moment, though.

	sudo apt-get install curl apache2 mysql-common mysql-admin mysql-server mysql-client sqlite3 php5 php5-common php5-gd php5-memcached php5-suhosin php5-imagick php5-curl php5-cli php5-common php5-mcrypt php5-intl php-pear php-soap php5-dev php5-sqlite php-apc phpmyadmin sqlite mongodb git-core python-software-properties

	sudo pecl install mongo
    
    # php5-devel instead of php5 ??  Sometimes PEAR/PECL doesn't get installed...
    # install supervisor
	
### App Installation/Deployment ###

1. Run `curl -s http://getcomposer.org/installer | php` to get composer, if your system doesn't already have it.  Try `which composer` to see if it does.
	1. if that didn't work, install curl, or use `wget`
	2. You may have to edit some settings in `php.ini` to allow execution of `.phar` files
2. Run `php composer.phar install` to have it install application dependencies. Some systems have a permanent copy of composer installed, so `composer install` should work fine.
3. Symlink `app/console` into `/usr/local/bin/ayamel`
    1. This is for the `supervisor` scripts, which use `ayamel` as the command name
    2. And it's just a nice shortcut for running the commands in the app
4. Make sure values in `app/config/parameters.yml` are correct for your deployment environment.  This file **SHOULD NOT** be included in the repository, you may need to create it yourself.  Check the subsection below for a default `parameters.yml`.  There is a `parameters.default.yml` included - copy that to `parameters.yml` to get started.  That file lists the configs that need to be modified on a per-deployment basis.
5. If this is a linux machine, run the `bin/linux_ayamel_user_setup` script to setup special application system users
    1. If you are going to manually run commands via the `ayamel` program, also add yourself to the `ayamel` group to avoid causing file permission conflicts
6. Make sure the web server is pointed to `web/` as the document root.
7. Clear the Symfony caches for dev and prod environments:

        ayamel cache:clear
        ayamel cache:clear --env=prod
    
    > If you have problems in this stage, manually remove everything in `app/cache/` and retry the commands.  If you get real desperate just... use `sudo` ... :)

8. Setup the local environment (if necessary):
    1. Make sure `rabbitmq-server` is running (if connecting to `localhost`)
    2. Make sure `mongod` is running (if connecting to `localhost`)
    3. Start any asyncronous tasks needed by using the shell scripts in `bin/`
        1. For example, for asyncronous transcoding, run `bin/transcoding_start`
        2. You can then run `bin/transcoding_manage` to see details for the processes started
9. That should be it.

## Deployment ##

If you plan on using running the API server in a production deployment - we highly recommend.

## Roadmap ##

See `TODO.md`.
