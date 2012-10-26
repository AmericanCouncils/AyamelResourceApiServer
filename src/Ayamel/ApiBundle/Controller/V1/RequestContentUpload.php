<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class RequestContentUpload extends ApiController {
    
    /**
     * Request a valid content upload url for a resource.  Note that a content upload url is only valid for one use only.
     * For more information on this, see the documentation for the upload route: `POST: /resources/{id}/content/{token}`
     *
     * @param string $id 
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Get a content upload url.",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
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
        
        $url = $this->container->get('router')->generate('api_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true);
        
        return $this->createServiceResponse(array('content_upload_url' => $url), 200);
    }
    
}
