# AyamelResourceBundle #

This bundle defines the base Resource objects and provides MongoDB mappings for data persistence, as well as custom repository classes
for common operations.

It also provides a pluggable mechanism for deriving resource documents from external sources.
	
## Todo list ##

* Remove references to old validation stuff
* implement bytes property of FileReference in API logic properly
* Make HEAD requests on http/https URI/files to get mime/bytes info
* Test Providers (where possible) and DelegatingProvider
