# AyamelTranscodingBundle #

This bundle adds a subscriber that listens for new files uploaded via the Resource API.  Then it uses the ACTranscodingBundle to schedule a transcode job to be handled asynchronously.  It also provides a custom transcode logger.

## TODO ##

* Listen to handle_content event and resource modified event - if handled content, then enable, so on resource_modify will schedule transcode event for "original" files with internal uri