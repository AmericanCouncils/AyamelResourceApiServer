# Library Implementation Steps #

These are the general steps, notes on actual implementation follow.

1. basic resource API CRUD routes (no file upload) - in process
2. file upload
3. backend transcoding
4. search index + api search routes
5. api security and client management
6. public front-end for api clients with documentation and registration instructions


## Implementation details ##

Per each step, here's a general outline of how we'd like to go about implementing.

### Step 1 (in progress) ###

Get the basics of exchanging the resource JSON structure down.

* `DoctrineMongoDbBundle`
	* provide base MongoDB support
* `AyamelResourceBundle`
	* integrate resource object structure with MongoDB first
	* consider providing an interface for a plugable backend for storage (can come back and refactor for this later if necessary)
* `ACWebServicesBundle`
	* provides api workflow abstraction, format/error handling
	* consider using FOSRestBundle instead, depending on what they do
* `AyamelResourceApiBundle`
	* integreate with `ACWebServicesBundle to expose public api routes for manipulating 

### Step 2 ###

Deal properly with content uploaded by the client, which includes physical files, but also URIs to other resource that we may treat in a special manner, such as YouTube links.

* Modify `AyamelResourceBundle`
	* add ability to handle raw file uploads and manage the filesystem
	* add ability to derive resource structures from special URI schemes
		* youtube://_q23asdfADF33~
* Add other Bundles if necessary to integrate the APIs we care about supporting
	* `AyamelYoutubeResourceBundle`
	* Vimeo
	* ... others?

### Step 3 ###

Integrate back-end transcoding for any content that's uploaded.

* `ACMutateBundle`
	* plug in the `AC\Mutate` transcoding library into Symfony
* `ACMutateQueueBundle`
	* provide an asyncronous transcode job queueing system
* `AyamelMutateResourceBundle`
	* integrate the queue system with the `AyamelResourceBundle` to handle manipulating resources asyncronously
	* Or... just modify `AyamelResourceBundle` to provide whatever is needed

### Step 4 ###

Integrate either Apache Solr or ElasticSearch... we're leaning heavily towards ElasticSearch since it fits better conceptually with the way we're intending to structure objects in Mongodb.

* Integrate `FOQElasticaBundle`
* Modify `AyamelResourceBundle` to integrate with `FOQElasticaBundle` or create a separate `AyamelResourceElasticaBundle`
* Modify `AyamelResourceApiBundle` to add search routes to API

### Step 5 ###

Integrate proper API security around the exposed API routes and provide interfaces for managing API clients.

* If a decent abstractable system can be realistically implemented, provide the structure for doing so in `ACWebServicesBundle`
* Plug in another bundle (hopefully) or write our own API security system (hopefully not)
	* `FOSOAuthServerBundle`
* modify `AyamelResourceApiBundle` to integrate properly with security system as necessary

### Step 6 ###

Build a basic frontend so API clients can search/view public resource objects, view API terms and documentation, and register for and manage API keys.
