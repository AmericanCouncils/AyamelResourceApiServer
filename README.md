# README #

This contains installation instructions, and a basic explanation of how the project is structured.

## Installation ##

1. run `php composer.phar install` to fetch dependencies

## Structure (work-in-progress) ##

The code written specifically for this project is contained under `/src`.  It currently consists of 3 main bundles:

* `ACWebServicesBundle` - Provides event listeners to handle API routes, which enable content negotiation, error handling, and allow creation of format-agnostic controllers.  Note that this bundle may be replaced by the `FOSRestBundle` in the future, depending on what happens with its development.
* `AyamelResourceBundle` - Provides base Resource classes, with persistence meta data for use with MongoDB.  Will be updated along the way to provide support for other things that need to be plugged into, such as ElasticSearch and Mutate for transcoding.
* `AyamelResourceApiBundle` - Provides actual API routes and logic for interacting with resource objects.  Relies on `ACWebServicesBundle` for proper format and error handling.  Relies on `AyamelResourceBundle` for the actual objects.

## Roadmap ##

See `TODO.md`.