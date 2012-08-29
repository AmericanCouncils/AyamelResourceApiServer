# AyamelApiBundle #

This bundle provides generic api workflow, and api logic for accessing resource objects defined in the `AyamelResourceBundle`.

## Installation ##

0. You may need to run `php composer.phar update` from your project root to force dependencies to be downloaded.
1. Copy/paste this into your `app/config/routing.yml`:

		AyamelApiBundle:
		    resource: "@AyamelApiBundle/Resources/config/routing.yml"

2. Copy/paste this into your `app/config/config.yml`:

		imports:  
		    - { resource: '@AyamelApiBundle/Resources/config/config.yml' }
	
3. Modify your `AppKernel.php` to initialize this bundle:

		new Ayamel\ApiBundle\AyamelApiBundle(),
		
4. Clear and regenerate your app caches with the `app/console cache:clear` command.
	
## Documentation ##

Eventually there will be real documentation [here](Resources/docs/index.md).
	
## Todo list ##

* Implement JMSSerializer::deserialize for getting resource object from client requests (implement @ReadOnly annotations)
* Implement ServiceResponse and api-specific listeners, or implement FOSRest
* Replace docs by implementing `NelmioApiDocBundle`
* Implement remaining controllers