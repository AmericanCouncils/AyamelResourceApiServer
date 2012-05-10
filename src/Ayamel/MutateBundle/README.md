# AyamelMutateBundle #

The purpose of this bundle is to integrate the `ACMutateBundle` with the `AyamelResourceApiBundle` and `AyamelFilesystemBundle`.

To do this, the bundle registers file system event listeners to check for new files to add to a transcode queue.  A maintence routine is provided to transcode files in the queue, and update resources once the transcoding has completed.

## Todo ##

* create queue system
* properly configure services
* properly implement logger