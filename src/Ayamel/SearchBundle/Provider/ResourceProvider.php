<?php

namespace Ayamel\SearchBundle\Provider;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Elastica\Document;
use Ayamel\SearchBundle\ResourceIndexer;

class ResourceProvider implements ProviderInterface
{
	private $indexer;

	public function __construct(ResourceIndexer $indexer)
	{
		$this->indexer = $indexer;
	}

	
}
