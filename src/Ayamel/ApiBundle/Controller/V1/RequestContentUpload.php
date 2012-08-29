<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class RequestContentUpload extends ApiController {
    
    /**
     * Request a valid content upload url for a resource.
     *
     * @param string $id 
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Get a content upload url."
     * )
     */
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
			'content_upload_url' => $this->container->get('router')->generate('AyamelApiBundle_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true),
        );
        
    }
    
}
