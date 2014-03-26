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
    private $documentManager;

    protected static function pluck($field, $values)
    {
        return array_map(function ($v) use ($field) {
            return $v[$field];
        }, $values);
    }

    public function __construct(ResourceIndexer $indexer, $batch = 100, $documentManager)
    {
        $this->indexer = $indexer;
        $this->type = "Ayamel\ResourceBundle\Document\Resource";
        $this->batch = $batch;
        $this->documentManager = $documentManager;
    }

    public function populate(\Closure $loggerClosure = null, array $options = array())
    {

        if ($loggerClosure) {
            $loggerClosure('Indexing resources...');
        }

        $resourceRepo = $this->documentManager->getRepository("Ayamel\ResourceBundle\Document\Resource");
        $resources = $resourceRepo->findBy([]);
        $rawMongoDb = $this->documentManager->getConnection()->getMongo()->selectDB('ayamel_test');
        $cursor = $rawMongoDb->resources->find([], ['_id']);

        $ids = [];
        foreach ($cursor as $resource) {
            $ids[] = $resource['_id']->{'$id'};
        }

        if ($loggerClosure) {
            $loggerClosure(sprintf("Indexing %s resources.", count($ids)));
        }

        $this->indexer->indexResources($ids, $this->batch);

        if ($loggerClosure) {
            $loggerClosure("Finished indexing resources.");
        }
    }
}
