# README #

This contains installation instructions, and a brief explanation of how the project is structured.

## Installation ##

Brief instructions for both setting up a new server, and setting up the application.

### System Dependencies ###

These are the packages I installed on a clean Ubuntu 10.04 dev machine for testing, not all of them are actually being used at the moment, though.

	sudo apt-get install curl apache2 mysql-common mysql-admin mysql-server mysql-client sqlite3 php5 php5-common php5-gd php5-memcached php5-suhosin php5-imagick php5-curl php5-cli php5-common php5-mcrypt php5-intl php-pear php-soap php5-dev php5-sqlite php-apc phpmyadmin sqlite mongodb git-core python-software-properties

	sudo pecl install mongo
	
### App Installation ###

1. run `curl -s http://getcomposer.org/installer | php` to get composer
	1. if that didn't work, install curl, or use `wget`
	2. You may have to edit some settings in `php.ini` to allow execution of `.phar` files
2. run `php composer.phar install` to have it install dependencies

## App Architecture (for now) ##

The code written specifically for this project is contained under `/src`.  It currently consists of 3 main bundles:

* `ACWebServicesBundle` - Provides event listeners to handle input/output on API routes, which enables content negotiation, error handling, and allows creation of format-agnostic controllers.  Note that this bundle may be replaced by the `FOSRestBundle` in the future, depending on what happens with its development.
* `AyamelResourceBundle` - Provides base Resource classes, with persistence meta data for use with MongoDB, and serialization meta data for use when the object is converted to various formats for the client.  Will be updated along the way to provide support for other things that need to be plugged into, such as ElasticSearch and Mutate for transcoding.
* `AyamelApiBundle` - Provides actual API routes and logic for interacting with resource objects.  Relies on `ACWebServicesBundle` for proper format and error handling.  Relies on `AyamelResourceBundle` for the actual objects.  Will be updated along the way to provide nice human-readable documentation, a place to download client wrappers for the raw api, and deal with client authentication/authorization schemes.

The majority of the API works by firing events when actions of note occur.  Features like file handling, file transcoding and search indexing, work by listening by registering event listeners for said events.

More details can be found in `TODO.md`.

## Roadmap ##

See `TODO.md`.