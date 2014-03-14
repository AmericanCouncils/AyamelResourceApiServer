<?php

namespace Ayamel\SearchBundle\Provider;

use Ayamel\SearchBundle\ResourceIndexer;
use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;

/**
 * The ResourceProvider implements the necessary interface from FOSElasticaBundle to populate the search
 * index with Resources.  It uses the ResourceIndexer to convert Resources into Elastica Documents.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 **/
class ResourceProvider implements ProviderInterface
{
    private $indexer;
    private $type;

    public function __construct(ResourceIndexer $indexer, $batch = 100)
    {
        $this->indexer = $indexer;
        $this->type = $resourceType;
        $this->batch = 100;
    }

	public function populate(\Closure $loggerClosure = null, array $options = array())
    {

        if ($loggerClosure) {
            $loggerClosure('Indexing resources...');
        }

        throw new \RuntimeException("Need query for available IDs...");
        $ids = array();

        if ($loggerClosure) {
            $loggerClosure(sprintf("Indexing %s resources.", count($ids)));
        }

        $this->indexer->indexResources($ids, $this->batch);

        if ($loggerClosure) {
            $loggerClosure("Finished indexing resources.");
        }
    }
}
