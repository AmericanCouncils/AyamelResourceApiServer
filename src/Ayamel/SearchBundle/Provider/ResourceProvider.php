<?php

namespace Ayamel\SearchBundle\Provider;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Elastica\Document;
use Ayamel\SearchBundle\ResourceIndexer;

class ResourceProvider implements ProviderInterface
{
	private $indexer;
    private $type;

	public function __construct(ResourceIndexer $indexer, Type $resourceType)
	{
		$this->indexer = $indexer;
        $this->type = $resourceType;
	}

	public function populate(\Closure $loggerClosure = null)
    {
        //TODO: query for ids, loop and update, with batching
        $this->indexer->indexResourceById(23);

        if ($loggerClosure) {
            $loggerClosure('Indexing resources...');
        }

        $document = new Document();
        $document->setData(array('username' => 'Bob'));
        $this->type->addDocuments(array($document));
    }
}
