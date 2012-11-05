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
4. Make sure values in `app/config/parameters.yml` are correct for your deployment environment.  This file **SHOULD NOT** be included in the repository, you may need to create it yourself.  Check the subsection below for a default `parameters.yml`
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

#### Default `parameters.yml` ####

If your installation is completely new, you'll need to provide the `parameters.yml` file, which contains configuration specific to your deployment environment.  If this file already exists, don't change it, and whatever you do - **DO NOT ADD IT TO YOUR REPOSITORY**.

The specific values in this file should be changed to suit your needs.

    #copy to `app/config/parameters.yml`
    parameters:
        database_driver: pdo_sqlite
        database_path: %kernel.root_dir%/app/data/db.sqlite
        database_host: localhost
        database_port: '9999'
        database_name: ayamel
        database_user: root
        database_password: null
        mailer_transport: smtp
        mailer_host: localhost
        mailer_user: null
        mailer_password: null
        locale: en
        secret: 1b577a9e247bf2c882bd861e1e825d83
    
        # Added by evan for local environment #
    
        ## File upload handling ##
        ayamel.filesystem.local_filesystem.root_dir: %kernel.root_dir%/files/resource_uploads
        ayamel.filesystem.local_filesystem.secret: 34565dfg897jksksk4wk4ksdfkj34                #WARNING: If you are adding a production machine, make sure this value is not changed from the other machines.
        ayamel.filesystem.local_filesystem.base_uri: http://localhost/ayamel/web/files
    
        ## API Docs
        nelmio_api_doc.sandbox.endpoint: http://localhost/ayamel/web/index_dev.php

        ## Transcoding support
        transcoding.ffmpeg.path: /usr/bin/ffmpeg
        transcoding.handbrake.path: /usr/local/bin/HandBrakeCLI
        ayamel.transcoding.temp_directory: %kernel.root_dir%/files/tmp

## App Architecture (for now) ##

The code written specifically for this project is contained under `/src`.  It currently consists of 3 main bundles:

* `ACWebServicesBundle` - Provides event listeners to handle input/output on API routes, which enables content negotiation, error handling, and allows creation of format-agnostic controllers.  Note that this bundle may be replaced by the `FOSRestBundle` in the future, depending on what happens with its development.
* `AyamelResourceBundle` - Provides base Resource classes, with persistence meta data for use with MongoDB, and serialization meta data for use when the object is converted to various formats for the client.  Will be updated along the way to provide support for other things that need to be plugged into, such as ElasticSearch and Mutate for transcoding.
* `AyamelApiBundle` - Provides actual API routes and logic for interacting with resource objects.  Relies on `ACWebServicesBundle` for proper format and error handling.  Relies on `AyamelResourceBundle` for the actual objects.  Will be updated along the way to provide nice human-readable documentation, a place to download client wrappers for the raw api, and deal with client authentication/authorization schemes.

The majority of the API works by firing events when actions of note occur.  Features like file handling, file transcoding and search indexing, work by listening by registering event listeners for said events.  As much work as possible is processed out of the request/response cycle by queuing messages in `RabbitMQ`, which are processed asyncronously as received.  This allows as much work as possible to be distrubuted through more processes on more machines, and helps the API perform better from a client perspective.

## Roadmap ##

See `TODO.md`.
