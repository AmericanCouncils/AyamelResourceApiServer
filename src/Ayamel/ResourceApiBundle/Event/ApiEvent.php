<?php

namespace Ayamel\ResourceApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event {
	
    protected $resource = false;
    
    public function __construct(Resource $resource = null) {
        if($resource) {
            $this->resource = $resource;
        }
    }
    
    public function getResource() {
        return $this->resource;
    }
    
}
