<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class ModifyResource extends ApiController {
	
	public function executeAction($id) {
		throw $this->createHttpException(501);
		
		//get the resource
		$resource = $this->getRequestedResourceById($id);
		
		//check for deleted resource
		if(null != $resource->getDateDeleted()) {
			return $this->returnDeletedResource($resource);
		}
		
		//get the resource validator
		$validator = $this->container->get('ayamel.resource.validator');
		
		//decode incoming data
		$data = $validator->decodeIncomingDataByRequest($this->getRequest());
		
		//validate incoming fields
		$resource = $validator->modifyResource($resource, $data);
		
		
		//save it
		
		//return it
		
	}
	
}
