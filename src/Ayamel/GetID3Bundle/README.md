# AyamelGetID3Bundle #

This bundle integrates the `getid3` library with the Ayamel API filesystem, in order to enhance the metadata about file references managed by the API.

## Implementation ##

The bundle registers an event listener with the filesystem that will analyze a file using `getid3` and inject the returned properties into the file reference
any time a file reference is newly added or retrieved.  All results are cached in the filesystem to avoid analyzing the file every time it's retrieved.