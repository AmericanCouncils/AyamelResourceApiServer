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
	
## Roadmap ##

In the future, this bundle should be split into two bundles.  The generic api workflow bundle will be renamed to `ACWebServicesBundle`, and specific Ayamel api functionality will remain in `AyamelApiBundle`.

## Todo list ##

* implement controllers