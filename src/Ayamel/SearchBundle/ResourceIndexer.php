<?php

namespace Ayamel\SearchBundle;

class ResourceIndexer
{
	public function __construct(DocumentManager $dm, Type $type)
	{

	}

	public function indexResourceById($id)
	{
		//get resource
		//get relations
		//get related resources
		//scan related resources for text representations
		//use guzzle to get all text
		//fill in content_* fields
		//update document
	}

	public function createSearchDocumentForResource(Resource $resource)
	{
		
	}
}
