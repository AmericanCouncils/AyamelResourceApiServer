# ACWebServicesBundle #

This bundle provides generic api workflow tools for developing RESTful apis.  Provided are event listeners for handling API routes, which facilitate writing output-format-agnostic controllers, content negotiation and error handling.

*Warning:*  This bundle may be removed in the future, and replaced with `FOSRestBundle`.  This may or may not be done, depending on where development with bundle that is heading.

## Installation ##

0. Use composer	
	
## Usage ##

To activate the event listeners that handle API requests, provide the `ac.webservices.api_paths` config to specify an array of API routes you want to be handled.  If it matches, event listeners will be added to invoke content format negotiation, error handling, and view handling, which will allow you to return raw data structures and objects that will automatically be encoded into the requested format.

## Todo list ##

* Implement client object validator
* Implement ServiceResponse class
