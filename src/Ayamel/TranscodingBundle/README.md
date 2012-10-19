# AyamelTranscodingBundle #

This bundle adds a subscriber that listens for new files uploaded via the Resource API.  Then it uses the ACTranscodingBundle to schedule a transcode job to be handled asynchronously.  It also provides a custom transcode logger to help with tracking server overhead for clients that upload files.

## TODO ##

* finish `Mapper` for dealing with preset configuration & implement convenience methods
    * and test the damn thing
* move logic from `RabbitMQ\Consumer` to `TranscodeManager`
