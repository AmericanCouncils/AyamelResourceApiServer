# AyamelTranscodingBundle #

This bundle adds support for transcoding Resource file uploads.  In order to do this, it integrates the ACTranscodingBundle with the AyamelApiBundle by registering asynchronous transcoding jobs via the RabbitMQBundle.

## Implementation ##

The bundle adds a subscriber that listens for new files uploaded via the Resource API.  Then it registers a transcode job to be handled asynchronously.  It also provides a custom transcode logger to help with tracking server overhead for clients that upload files.

## TODO ##

* implement client notifications after a resource finishes transcode - maybe implement various TranscodingManager::EVENTs
for future hooks