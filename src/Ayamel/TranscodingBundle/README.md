# AyamelTranscodingBundle #

This bundle adds support for transcoding Resource file uploads.  In order to do this, it integrates the ACTranscodingBundle with the AyamelApiBundle by registering asynchronous transcoding jobs via the RabbitMQBundle.

## Implementation ##

The bundle adds a subscriber that listens for new files uploaded via the Resource API.  Then it registers a transcode job to be handled asynchronously.  It also provides a custom transcode logger to help with tracking server overhead for clients that upload files.

Resources can be transcoded directly via the command `api:resource:transcode [resourceId]`.

## Configuration ##

The config regarding transcoding presets, and the preset map is not loaded by importing the file directly, rather it is loaded
separately in a container extension.  The reason for this is that it needs to be modified for the test environment to include
a few extra mappings that are only applicable for testing.
