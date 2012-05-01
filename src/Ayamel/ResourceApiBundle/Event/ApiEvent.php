<?php

namespace Ayamel\ResourceApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event {
	
    protected $resource = false;
    
    public function getResource() {
        return $this->resource;
    }
    
    public function setResource(Resource $resource) {
        $this->resource = $resource;
    }
    
}
