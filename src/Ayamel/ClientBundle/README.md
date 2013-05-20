# AyamelClientBundle #

This bundle provides support for dealing with API clients.  This includes

* A model for clients
* An interface for managing clients
    * `ClientLoaderInterface` ? or
    * `ClientManagerInterface`
* Services for loading and retrieving available client data
    * `ayamel.client_manager` - for loading actual client information
    * `ayamel.client_loader` - for retrieving specific client from context like a request
* Config validation

## Roadmap ##

Inital support for API client infomration is read-only.  All data for the initial phase is hard-coded configuration. This
implementation will be replaced by real storage and an API.

