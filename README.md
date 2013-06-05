# Ayamel Media API #

This project is an API server for managing multimedia resources.  The primary focus of the project is to provide a 
mechanism for institutions to host and search multimedia resources that are useful for language learners. The API served
by this application can be used by other applications to simplify managing multimedia, and also to facilitate
sharing resources accross institutions and applications.  The project includes several major features:

* hosting of multimedia
* normalization of existing multimedia resources on the web
* searchability of multimedia
* transcoding of hosted multimedia files into multiple formats

The project is hosted at [api.ayamel.org](http://api.ayamel.org), but as it is open source, it can be deployed
and implemented elsewhere.

The project is still in the relatively early stages of development, so there are key features which have not yet been implemented.  We plan to have the core feature set implemented and stable by Winter 2013.

## Concepts ##

The API makes use of certain key concepts that you must understand in order to use it effectively.

* **Resource** - A Resource is basically a metadata container that references actual multimedia content.  Actual content could be a series of files on a server, or links to other resources on the web, such as YouTube videos.
* **Relation** - A Relation is a metadata structure that defines how one resource relates to another.  Relations are critical because search relies on them.  For example, if you want to search for a video, and there is a transcript of that video, then search will return hits on both the transcript and the video.  This works because of relations - the video is a resource, and the transcript is also a resource, the relation lets the search indexer know that these two resources should reference each other during search.  Relations are also used to define certain types of resources that don't contain actual content.  For example a collection of videos about a particular theme may just contain relations that reference other individual resources.

## Technologies ##

Broadly speaking, the project is implemented in PHP using the [Symfony2](http://symfony.com/) framework.  Underlyingly it relies on several
key technologies:
dd
* [MongoDB](http://www.mongodb.org/) for data persistenece
* [RabbitMQ](http://www.rabbitmq.com/) for communication with asynchronous processes
* [ElasticSearch](http://www.elasticsearch.org/) for indexed search (*not yet implemented*)

## Contributing ##

Contributors are certainly welcome, please start discussions in the issue queue for bugs/feature discussion.

If you will contribute, please follow the [PSR coding standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), and make sure that new features are covered by thorough unit and/or functional tests.

## Tests ##

The test suite requires a bit of set up to run.  As many of the tests are full integration tests, running them requires a
more-or-less functioning environment - meaning you must have MongoDB and RabbitMQ properly installed, configured and running.

Tests are run with `phpunit`, and you'll need to clear the test cache before running them for the first time.

    app/console cache:clear --env=test
    phpunit

### App Installation/Deployment ###

Your server or series of servers will need:

* PHP 5.3.8+
* MongoDB
* RabbitMQ
* git
* composer

In order for the transcoding commands to work, you'll also need to install and configure `HandBrakeCLI` and `ffmpeg`.  The list of requirements for transcoding will evolve as support for more file formats is added.

To install and run the API on your own servers you'll need to configure a proper `parameters.yml` file in `app/config/`.  You may copy `parameters.default.yml` as a starting point - most of the config in that file will be specific to your deployment environment.

Once that is installed, use [composer](getcomposer.org) to install the necessary PHP dependencies.

    php composer.phar install

## Deployment ##

If you plan on using running the API server in a production deployment - we highly recommend using [ansible](http://ansible.cc/), as keeping the various technologies required properly configured can become cumbersome.

## Roadmap ##

See the [issue queue](https://github.com/AmericanCouncils/AyamelResourceApiServer/issues).
