<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceBundle\Document\Resource;

class DeleteResource extends ApiController {
    
    public function executeAction($id) {
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        
        //check for already deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        //TODO:
        // - remove files
        // - cancel pending transcode jobs
        // - remove from search
        
        //TODO: preserve some fields:
        // - contributer
        // - contributer name
        // - date added
        
        $resource = $this->container->get('ayamel.resource.manager')->deleteResource($resource);
        
        //return ok
        return array(
            'response' => array(
                'code' => 200
            ),
            'resource' => $resource
        );
    }

}
