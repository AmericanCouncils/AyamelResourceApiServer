# FilesystemBundle #

The bundle provides an interface for a filesystem to use for managing FileReference objects based on Resource object ids.  The bundle registers an event subscriber for handling uploaded content to the Resource Api.

Currently, the only implementation of the FilesystemInterface is a local implementation.  However, the interface was designed as such to leave opporuntiy for implementing a 3rd party service for handling files, if necessary or desired.

A FilesystemManager can wrap another instance of a Filesystem, and fires pre/post events for all file-related actions.

## Services ##

> `ayamel.api.filesystem` is the main filesystem service, it's exact implementation should be defined in the configuration.

By default the `ayamel.api.filesystem` service is defined as an instance of `FilesystemManager`, which receives an instance of `LocalFilesystem`.  The `LocalFilesystem` instance needs 3 config parameters:

* root directory on filesystem
* string secret to use in file organization scheme
* optional public URI that points to the same location as the root directory for web accessible files

## Todo ##

* implement commands