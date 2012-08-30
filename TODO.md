# Library Implementation Steps #

These are the general steps, notes on actual implementation follow.

* Fix docs template to integrate more nicely w/ app
* Refactor API input to use JMS deserialization
* Add transcoding backend
* Add search indexing w/ ElasticSearch
* Add API client management

## Implementation details ##

Per each step, here's a general outline of how we'd like to go about implementing.

### Step 1 (done) ###

Get the basics of exchanging the resource JSON structure down.

* `DoctrineMongoDbBundle`
	* provide base MongoDB support
* `AyamelResourceBundle`
	* integrate resource object structure with MongoDB first
	* consider providing an interface for a plugable backend for storage (can come back and refactor for this later if necessary)
* `ACWebServicesBundle`
	* provides api workflow abstraction, format/error handling
	* consider using FOSRestBundle instead, depending on what they do
* `AyamelApiBundle`
	* integrate with `ACWebServicesBundle to expose basic CRUD api routes for manipulating resources

### Step 2 (in progress) ###

Deal properly with content uploaded by the client, which includes physical files, but also URIs to other resource that we may treat in a special manner, such as YouTube links.

* Modify `AyamelResourceBundle`
	* add ability to manage the filesystem
	* add ability to derive resource structures from special URI schemes (most likely by connecting to specific APIs and querying an object by ID)
		* youtube://_q23asdfADF33~
* Create other Bundles as necessary to integrate the APIs we care about supporting
	* `AyamelYoutubeResourceBundle`
	* `AyamelVimeoResourceBundle`
	* ... others?
* Implement routes in `AyamelApiBundle` to handle raw file uploads from clients

### Step 3 (in progress) ###

Integrate back-end transcoding for any content that's uploaded.

* `ACTranscodingBundle`
	* plug in the `AC\Mutate` transcoding library into Symfony
* `SonataNotificationBundle`
	* provide an asyncronous transcode job queueing system
* `AyamelTranscodingBundle`
	* integrate the queue system with the `AyamelResourceBundle` to handle manipulating resources asyncronously
	* Or... just modify `AyamelResourceBundle` to provide whatever is needed

### Step 4 ###

Integrate either Apache Solr or ElasticSearch... we're leaning heavily towards ElasticSearch since it fits better conceptually with the way we're intending to structure objects in Mongodb.

* Integrate `FOQElasticaBundle`
* Modify `AyamelResourceBundle` to integrate with `FOQElasticaBundle` or create a separate `AyamelResourceElasticaBundle`
* Modify `AyamelApiBundle` to add search routes to API

### Step 5 ###

Integrate proper API security around the exposed API routes and provide interfaces for managing API clients.

The big question I have regarding this step is: How important is security?  If we need SSL, we may as well just do OAuth, or at least part of it.  If not, simple API Key checking would probably be fine.

* If a decent abstractable system can be realistically implemented, provide the structure for doing so in `ACWebServicesBundle`
	* This would most likely be for simple API key authentication, if we go that route
* Plug in another bundle (hopefully) or write our own API security system (hopefully not)
	* `FOSOAuthServerBundle` - ability to do just 2-legged OAuth?
* modify `AyamelApiBundle` to integrate properly with security system as necessary
