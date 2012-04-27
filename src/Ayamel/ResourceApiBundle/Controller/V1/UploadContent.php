<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class UploadContent extends ApiController {
    
    public function executeAction($id, $token) {
        throw $this->createHttpException(501);
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
                
        //check for deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        //if using the token fails, 401
        try {
            $this->container->get('ayamel.api.upload_token_manager')->useTokenForId($id, $token);
        } catch (\Exception $e) {

            $this->container->get('ayamel.api.upload_token_manager')->removeTokenForId($id);

            throw $this->createHttpException(401, $e->getMessage());
        }
        
        //
        
        //return 202 on success
    }

}