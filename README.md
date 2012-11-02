# README #

This contains installation instructions, and a brief explanation of how the project is structured.

## Installation ##

Brief instructions for both setting up a new server, and setting up the application.

### System Dependencies ###

These are the packages I installed on a clean Ubuntu 10.04 dev machine for testing, not all of them are actually being used at the moment, though.

	sudo apt-get install curl apache2 mysql-common mysql-admin mysql-server mysql-client sqlite3 php5 php5-common php5-gd php5-memcached php5-suhosin php5-imagick php5-curl php5-cli php5-common php5-mcrypt php5-intl php-pear php-soap php5-dev php5-sqlite php-apc phpmyadmin sqlite mongodb git-core python-software-properties

	sudo pecl install mongo
    
    # php5-devel instead of php5 ??  Sometimes PEAR/PECL doesn't get installed...
    # install supervisor
	
### App Installation ###

1. run `curl -s http://getcomposer.org/installer | php` to get composer
	1. if that didn't work, install curl, or use `wget`
	2. You may have to edit some settings in `php.ini` to allow execution of `.phar` files
2. run `php composer.phar install` to have it install application dependencies
3. Symlink `app/console` into `/usr/local/bin/ayamel`
    1. This is for the `supervisor` scripts, which use `ayamel` as the command name
4. Make sure values in `app/config/parameters.yml` are correct for your deployment environment.  This file **SHOULD NOT** be included in the repository, you may need to create it yourself.
5. Run the `bin/ayamel_user_setup` script
    1. If you are going to manually run commands via the `ayamel` program, also add yourself to the `ayamel` group
! 6. Add the web server process owner to the `ayamel` group.  This user is most likely called `www-data` or `www`
7. Make sure the web server is pointed to `web/`.
8. Clear the Symfony caches for dev and prod environments:

        ayamel cache:clear
        ayamel cache:clear --env=prod
    
    > If you have problems in this stage, manually remove everything in `app/cache/` and retry the commands.  If you get real desperate just... use `sudo` ... :)

9. Setup the local environment (if necessary):
    1. Make sure `rabbitmq-server` is running (if connecting to `localhost`)
    2. Make sure `mongod` is running (if connecting to `localhost`)
    3. Start any asyncronous tasks needed by using the shell scripts in `bin/`
10. That should be it.

## App Architecture (for now) ##

The code written specifically for this project is contained under `/src`.  It currently consists of 3 main bundles:

* `ACWebServicesBundle` - Provides event listeners to handle input/output on API routes, which enables content negotiation, error handling, and allows creation of format-agnostic controllers.  Note that this bundle may be replaced by the `FOSRestBundle` in the future, depending on what happens with its development.
* `AyamelResourceBundle` - Provides base Resource classes, with persistence meta data for use with MongoDB, and serialization meta data for use when the object is converted to various formats for the client.  Will be updated along the way to provide support for other things that need to be plugged into, such as ElasticSearch and Mutate for transcoding.
* `AyamelApiBundle` - Provides actual API routes and logic for interacting with resource objects.  Relies on `ACWebServicesBundle` for proper format and error handling.  Relies on `AyamelResourceBundle` for the actual objects.  Will be updated along the way to provide nice human-readable documentation, a place to download client wrappers for the raw api, and deal with client authentication/authorization schemes.

The majority of the API works by firing events when actions of note occur.  Features like file handling, file transcoding and search indexing, work by listening by registering event listeners for said events.  As much work as possible is processed out of the request/response cycle by queuing messages in `RabbitMQ`, which are processed asyncronously as received.  This allows as much work as possible to be distrubuted through more processes on more machines, and helps the API perform better from a client perspective.

## Roadmap ##

See `TODO.md`.
