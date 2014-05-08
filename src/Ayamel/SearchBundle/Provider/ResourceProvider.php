<?php

namespace Ayamel\SearchBundle\Provider;

use Ayamel\SearchBundle\ResourceIndexer;
use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Ayamel\SearchBundle\Exception\BulkIndexException;

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

        $rawMongoDb = $this->documentManager->getDocumentDatabase('Ayamel\ResourceBundle\Document\Resource')->getMongoDB();

        $cursor = $rawMongoDb->resources->find([], ['_id']);

        $ids = [];
        foreach ($cursor as $resource) {
            $ids[] = $resource['_id']->{'$id'};
        }

        if ($loggerClosure) {
            $loggerClosure(sprintf("Indexing %s resources.", count($ids)));
        }

        try {
            $this->indexer->indexResources($ids, (int) $options['batch-size']);
        } catch (BulkIndexException $e) {
            if ($loggerClosure) {
                $loggerClosure(sprintf("Finished indexing, skipped [%s] resources.", $e->getCount()));

                if ($options['verbose']) {
                    foreach ($e->getMessages() as $id => $message) {
                        $loggerClosure(sprintf("Index failed/skipped for [%s]: %s", $id, $message));
                    }
                }
            }
        }

        if ($loggerClosure) {
            $loggerClosure("Finished indexing resources.");
        }
    }
}
