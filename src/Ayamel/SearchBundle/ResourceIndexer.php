<?php

namespace Ayamel\SearchBundle;

class ResourceIndexer
{
    public function __construct()
    {

    }

    public function indexResourceById($id)
    {
        throw new \RuntimeException("pwn3d");
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
        throw new \RuntimeException("pwn3d");
    }
}
