<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class ModifyResource extends ApiController {
	
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
		
		//validate incoming fields
		$resource = $validator->modifyAndValidateExistingResource($resource, $data);
		
		//modify fields controlled by the resource library
		$resource->setDateModified(new \DateTime());
				
		//save it
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
		
		//return it
		$content = array(
			'response' => array(
				'code' => 200,
			),
			'resource' => $resource
		);
		
		return $content;
	}
	
}
