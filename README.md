# Ayamel Media API #

[![Build Status](https://travis-ci.org/AmericanCouncils/AyamelResourceApiServer.png?branch=master)](https://travis-ci.org/AmericanCouncils/AyamelResourceApiServer)

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

Broadly speaking, the project is implemented in PHP using the [Symfony2](http://symfony.com/) framework.  Underlyingly it relies on 
several key technologies:

* [MongoDB](http://www.mongodb.org/) for data persistenece
* [RabbitMQ](http://www.rabbitmq.com/) for communication with asynchronous processes
* [ElasticSearch](http://www.elasticsearch.org/) for indexed search
* [ffmpeg](http://www.ffmpeg.org/) and related tools for transcoding files

## Development and Testing ##

The project has a number of dependencies and requires a bit of setup to run and test.  For this reason, there is a vagrant environment
configured for development. The vagrant dev machine installs [ansible](http://docs.ansible.com/intro_installation.html) on the VM, then runs it to install all other dependencies.  The machine is configured for development and running the tests.

This is all you should need in order to develop:

* `cd <path/to/ayamel>` - go to this project's root directory on your machine
* `vagrant up`          - start the vagrant vm and set it up
* `vagrant ssh`         - connect to the vm
* `cd /vagrant`         - REMOVE
* `bin/phpunit --exclude-group=transcoding` - run the tests

At this point you can edit files in the project and re-run the tests whenever.

TODO: notes about connecting to the VM - and test ApiKey

> Note: The first time you run `vagrant up`, it will take a fair amount of time to set the machine up.

> Note: Video transcoding tests are being ignored for the moment, due to ffmpeg being a pain to install.

## Contributing ##

Contributors are certainly welcome, please start discussions in the issue queue for bugs/feature discussion.

If you will contribute, please follow the [PSR coding standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), and make sure that new features are covered by thorough unit and/or functional tests.

## Roadmap ##

See the [issue queue](https://github.com/AmericanCouncils/AyamelResourceApiServer/issues).
