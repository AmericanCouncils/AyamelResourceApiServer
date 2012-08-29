<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ModifyResource extends ApiController {
    

    /**
     * Accepts data from a request object, attempting to modify a specific resource object.
     * 
     * @ApiDoc(
     *      resource=true,
     *      description="Modify a resource",
     *      input="Ayamel\ResourceBundle\Document\Resource"
     * );
     *
     * @param string $id 
     */
    public function executeAction($id) {
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        
        //check for deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        //get the resource validator
        $validator = $this->container->get('ayamel.api.client_data_validator');
        
        //decode incoming data
        $data = $validator->decodeIncomingResourceDataByRequest($this->getRequest());
        
        //validate incoming fields and modify resource
        $resource = $validator->modifyAndValidateExistingResource($resource, $data);
                        
        //save it
        try {
            $this->container->get('ayamel.resource.manager')->persistResource($resource);
        } catch (\Exception $e) {
            throw $this->createHttpException(400, $e->getMessage());
        }
        
        //notify rest of system of modified resource
        $event = new ApiEvent($resource);
        $this->container->get('ayamel.api.dispatcher')->dispatch(Events::RESOURCE_MODIFIED, $event);
        
        //return it
        //TODO: return $this->createServiceResponse($data, 200);
        $content = array(
            'response' => array(
                'code' => 200,
            ),
            'resource' => $resource
        );
        
        return $content;
    }
    
}
