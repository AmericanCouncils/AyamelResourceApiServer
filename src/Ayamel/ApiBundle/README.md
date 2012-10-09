# AyamelApiBundle #

This bundle provides generic api workflow, and api logic for accessing resource objects defined in the `AyamelResourceBundle`.

## Architecture ##

TODO: describe generally how things are tied together
	
## Todo list ##

* Implement JMSSerializer::deserialize for getting resource object from client requests (implement @ReadOnly annotations)
* Implement ServiceResponse and api-specific listeners, or implement FOSRest
* Replace docs by implementing `NelmioApiDocBundle`
* Implement remaining controllers