<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns a resources object structure by its ID.
 */
class GetResource extends ApiController {
	
	public function executeAction($id) {
		
		//get the resource
		$resource = $this->getRequestedResourceById($id);
				
		//check for deleted resource
		if(null != $resource->getDateDeleted()) {
			return $this->returnDeletedResource($resource);
		}

		//assemble final content structure
		$content = array(
			'response' => array(
				'code' => 200,
			),
			'resource' => $resource,
		);
		
		return $content;
		//return \FOS\RestBundle\View::create($content, $httpStatusCode);
	}
}