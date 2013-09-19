<?php

namespace Ayamel\SearchBundle\Exception;

class BulkIndexException extends IndexException
{
    protected $ids = array();
    
    public function __construct(array $ids)
    {
        $this->ids = $ids;
        
        parent::__construct("Multiple Resources could not be indexed.");
    }
    
    public function getIds()
    {
        return $this->ids;
    }
}
