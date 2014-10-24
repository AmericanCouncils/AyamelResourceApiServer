# AyamelSearchBundle #

This bundle adds indexed search capabilities to Ayamel, backed by ElasticSearch.

It does this in the following ways:

* Provides config for the `FOSElasticaBundle` for configuring the ES index
* provides the `ayamel.search.resource_indexer` service, which is used in multiple places to actually populate the index
  * used in `fos:elastica:populate` command via a custom "provider"
  * runs in separate process as a rabbitmq "consumer"
* Provides Ayamel API listeners that publish messages to rabbitmq when resources and relations are modified
* Provides the `/search` route & controller to expose search to end clients via a simpler API
* Individual resources can be indexed via the `resource:index <resourceId>` command.

## Text Encoding ##

The content of parsable text-based files is stored in the ES index when a search document is created for a Resource.  However, if a 
text file contains non UTF-8 text, it must be converted to UTF-8 internally before it can be stored.  There is a service that does
this, and it is used internally by the indexer for this purpose.  However, detecting text encoding is very error prone and unreliable, so
a manual configuration of encoding priorities must be maintained.

To modify, or add new supported text formats, change the config `ayamel.search.text_converter_detect_order` value in `Resources/config/config.yml`: 

```
    #note that order matters, the first match is chosen
    ayamel.search.text_converter_detect_order: ['UTF-32', 'UTF-16', 'UTF-8', 'Windows-1251', 'Windows-1252']
```

Any time a new text format is added, a text file in that encoding, and a test for it should be added.