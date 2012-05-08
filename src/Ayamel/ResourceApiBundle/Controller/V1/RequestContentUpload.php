<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class RequestContentUpload extends ApiController {
    
    public function executeAction($id) {

        //get the resource
        $resource = $this->getRequestedResourceById($id);
                
        //check for deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
		$uploadToken = $this->container->get('ayamel.api.upload_token_manager')->createTokenForId($resource->getId());
        
        return array(
            'response' => array(
                'code' => 200,
            ),
			'content_upload_url' => $this->container->get('router')->generate('AyamelResourceApiBundle_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true),
        );
        
    }
    
}
