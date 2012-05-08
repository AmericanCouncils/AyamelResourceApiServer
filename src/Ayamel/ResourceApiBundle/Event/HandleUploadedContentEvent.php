<?php

namespace Ayamel\ResourceApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;

/**
 * Event for handling uplaoded content.  Setting the Resource via "setResource()" will stop propagation.
 *
 * @author Evan Villemez
 */
class HandleUploadedContentEvent extends ApiEvent {
	
    protected $content;

    protected $type;
    
    public function __construct(Resource $resource, $contentType, $contentData) {
        parent::__construct($resource);
        
        $this->type = $contentType;
        $this->content = $contentData;
    }
    
    public function getContentType() {
        return $this->type;
    }
    
    public function getContentData() {
        return $this->content;
    }
    
    public function getResource() {
        return $this->resource;
    }
    
    /**
     * Setting the resource will stop propagation, as the assumption is that
     * if you set a Resource, you are declaring that you have handled the content
     * appropriately, thus there is no need to call other listeners.
     *
     * @param Resource $resource 
     */
    public function setResource(Resource $resource) {
        $this->resource = $resource;
        $this->stopPropagation();
    }
    
}
