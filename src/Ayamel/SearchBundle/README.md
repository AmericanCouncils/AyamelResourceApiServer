# AyamelSearchBundle #

This bundle adds indexed search capabilities to Ayamel, backed by ElasticSearch.

It does this in the following ways:

* Provides config for the `FOSElasticaBundle` for configuring the ES index
* provides the `ayamel.search.resource_indexer` service, which is used in multiple places to actually populate the index
  * used in `fos:elastica:populate` command via a custom "provider"
  * runs in separate process as a rabbitmq "consumer"
* Provides Ayamel API listeners that publish messages to rabbitmq when resources and relations are modified
* Provides the `/search` route & controller to expose search to end clients via a simpler API
* Individual resources can be indexed via the "resource:index <resourceId>" command.
