<?php

namespace Ayamel\ResourceBundle\Event;

use Ayamle\ResourceBundle\Document\Resource;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Special base Event class that is container and Resource aware
 *
 * @author Evan Villemez
 */
class ResourceEvent extends Event {

    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
	protected $container;
    
    /**
     * @var object Ayamle\ResourceBundle\Document\Resource
     */
    protected $resource = false;
	
    /**
     * Event for passing Resource objects to the rest of the system.
     *
     * @param Resource $resource 
     */
    public function __construct(Resource $resource = null) {
        if(!is_null($resource)) {
            $this->resource = $resource;
        }
    }
    
    /**
     * Sets the Resource for the event, and stops propagation to other listeners.
     *
     * @param Resource $resource 
     */
    public function setResource(Resource $resource) {
        $this->resource = $resource;
        $this->stopPropagation();
    }
    
    /**
     * Return the Resource object processed by the event.
     *
     * @return Resource, or false if none is set
     */
    public function getResource() {
        return $this->resource;
    }
    
    /**
     * Set the container instance for listeners to use.
     *
     * @param ContainerInterface $container 
     */
    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
    }
    
}
