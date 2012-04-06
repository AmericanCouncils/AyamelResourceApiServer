<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;

class ModifyResource extends ApiController {
	
	public function executeAction($id) {
		throw $this->createHttpException(501);
		
		//get the resource
		$resource = $this->getRequestedResourceById($id);
		
		//check for deleted resource
		if(null != $resource->getDateDeleted()) {
			return $this->returnDeletedResource($resource);
		}
		
		//validate incoming fields
		
		//save it
		
		//return it
		
	}
	
}
