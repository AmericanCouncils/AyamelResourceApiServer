<?php

namespace Ayamel\ResourceBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;

/**
 * Extension of ResourceEvent specifically for use when retrieving events.
 *
 * @author Evan Villemez
 */
class GetResourceEvent extends ResourceEvent {

    protected $id;
    
    protected $queryParams;

    public function __construct($id = null) {
        if($id instanceof Resource) {
            parent::__construct($id);
        } else {
            $this->setId($id);
        }
    }
    
    public function setResourceId($id) {
        $this->id = $id;
    }
    
    public function getResourceId() {
        return $this->id;
    }
    
    public function setQueryParameters(array $array) {
        $this->queryParams = $array;
    }
    
    public function getQueryParameters() {
        return $this->queryParams;
    }
    
    public function setResource(Resource $resource) {
        $this->setResourceId($resource->getId());
        parent::setResource($resource);
    }
    
}
