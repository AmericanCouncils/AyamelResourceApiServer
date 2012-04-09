<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceBundle\Document\Resource;

class RemoveResource extends ApiController {
	
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
		
		//unset all fields (for now)
		foreach(get_class_methods($resource) as $method) {
			if(0 === strpos($method, 'set')) {
				$resource->$method(null);
			}
		}
		
		//set date deleted
		$resource->setDateDeleted(time());

		//save deleted resource
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
		
		//return ok
		return array(
			'response' => array(
				'code' => 200
			),
			'resource' => $resource
		);
	}

}
