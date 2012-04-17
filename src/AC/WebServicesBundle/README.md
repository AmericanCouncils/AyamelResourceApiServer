# ACWebServicesBundle #

This bundle provides generic api workflow tools for developing RESTful apis.  Provided are event listeners for handling API routes, which facilitate writing output-format-agnostic controllers, content negotiation and error handling.

*Warning:*  This bundle may be removed in the future, and replaced with `FOSRestBundle`.  This may or may not be done, depending on where development with bundle that is heading.

## Installation ##

0. You may need to run `php composer.phar update` from your project root to force dependencies to be downloaded.
1. Copy/paste this into your `app/config/routing.yml`:

		ACWebServicesBundle:
		    resource: "@ACWebServicesBundle/Resources/config/routing.yml"

2. Copy/paste this into your `app/config/config.yml`:

		imports:  
		    - { resource: '@ACWebServicesBundle/Resources/config/config.yml' }
	
3. Modify your `AppKernel.php` to initialize this bundle:

		new AC\WebServicesBundle\ACWebServicesBundle(),
		
4. Clear and regenerate your app caches with the `app/console cache:clear` command.
	
	
## Usage ##

To activate the event listeners that handle API requests, include `/rest/` in the path of the API routes you want to be handled.  If it matches, event listeners will be added to invoke content format negotiation, error handling, and view handling, which will allow you to return raw data structures and objects that will automatically be encoded into the requested format.

## Documentation ##

Eventually there will be real documentation [here](Resources/docs/index.md).
	
## Todo list ##

* Implement ServiceResponse class
* provide documentation generation commands based on definition files
* provide code client generation commands based on defintion files
* provide abstractable authentication solution ... maybe, there's lots of questions about this.