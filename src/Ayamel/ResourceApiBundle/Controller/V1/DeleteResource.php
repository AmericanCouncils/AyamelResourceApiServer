<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Ayamel\ResourceApiBundle\Controller\ApiController;

/**
 * Removes a Resource object by it's ID.
 *
 * @author Evan Villemez
 */
class DeleteResource extends ApiController {
    
    public function executeAction($id) {
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        
        //check for already deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        //TODO: preserve some fields:
        // - contributer
        // - contributer name
        // - date added
        
        $apiDispatcher = $this->container->get('ayamel.api.dispatcher');
        
        //notify system to remove content for resource
        $apiDispatcher->dispatch(Events::REMOVE_RESOURCE_CONTENT, new ApiEvent($resource));
        
        //remove from storage (sort of)
        $resource = $this->container->get('ayamel.resource.manager')->deleteResource($resource);
        
        //notify rest of system of deleted resource
        $apiDispatcher->dispatch(Events::RESOURCE_DELETED, new ApiEvent($resource));
        
        //return ok
        return array(
            'response' => array(
                'code' => 200
            ),
            'resource' => $resource
        );
    }

}
