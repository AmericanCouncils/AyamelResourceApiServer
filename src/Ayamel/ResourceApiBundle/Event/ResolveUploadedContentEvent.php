<?php

namespace Ayamel\ResourceApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\HttpFoundation\Request;

/**
 * This event fires when uploaded content is being resolved from an incoming request.  It is done via an event because
 * there are several different types of content, each of which is handled in a different way.  Listeners for this event
 * should parse the incoming request to determine which type of content is incoming, and set the parsed content data accordingly.
 * 
 * The parsed content data will be dispatched via a HandleUploadedContentEvent instance to listeners which will deal with the uploaded
 * content accordingly.
 *
 * @author Evan Villemez
 */
class ResolveUploadedContentEvent extends ApiEvent {
	
	protected $request;
    
    protected $resource;
	
    protected $type = false;

    protected $content = false;
    
    /**
     * Constructor requires the Resource which is being modified, and the incoming Http Request, which should 
     * contain content to be processed for the resource.
     *
     * @param Resource $resource 
     * @param Request $request 
     */
    public function __construct(Resource $resource, Request $request) {
        parent::__construct($resource);
        $this->request = $request;
    }
    
    /**
     * Set the type of content, should be a string.  This gets passed to the HandleUploadedContentEvent
     * in order to give listeners an easier way to determine which type of content is being handled.
     *
     * @param string $type 
     */
    public function setContentType($type) {
        $this->type = $type;
    }
    
    /**
     * Get the type of content to be handled.
     *
     * @return string, or false if not set
     */
    public function getContentType() {
        return $this->type;
    }
    
    /**
     * Set the parsed content data.  In order to set the content, you must first set the content 
     * type via "setContentType()".  Setting the content data will stop propagation to other listeners.
     *
     * @param mixed $data 
     * @return void
     */
    public function setContentData($data) {
        $this->content = $data;
        
        if(!$this->type) {
            throw new \RuntimeException("Cannot set content data without first setting the content type.");
        }

        $this->stopPropagation();
    }
    
    /**
     * Get the actual content to be handled.
     *
     * @return mixed, false if not set
     */
    public function getContentData() {
        return $this->content;
    }
    
    /**
     * Get the raw http request from which to derive any uploaded content content.
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest() {
        return $this->request;
    }
    
}
