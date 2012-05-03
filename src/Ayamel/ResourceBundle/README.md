# AyamelResourceBundle #

This bundle defines the base Resource objects and provides MongoDB mappings for data persistence.

## Installation ##

0. You may need to run `php composer.phar update` from your project root to force dependencies to be downloaded.
1. Copy/paste this into your `app/config/routing.yml`:

		AyamelResourceApiBundle:
		    resource: "@AyamelResourceApiBundle/Resources/config/routing.yml"

2. Copy/paste this into your `app/config/config.yml`:

		imports:  
		    - { resource: '@AyamelResourceApiBundle/Resources/config/config.yml' }
	
3. Modify your `AppKernel.php` to initialize this bundle:

		new Ayamel\ResourceApiBundle\AyamelResourceApiBundle(),
		
4. Clear and regenerate your app caches with the `app/console cache:clear` command.
	
## Documentation ##

Eventually there will be real documentation [here](Resources/docs/index.md).

## Todo list ##

* Change namespace Document to Model - move Mongo/JMS mappings, validation and L2 functionality into AyamelResourceApiBundle
    * Maybe... that is...