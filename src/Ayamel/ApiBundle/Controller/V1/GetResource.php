<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;

class GetResource extends ApiController {
	
	public function executeAction($id) {

		//get repository and find requested object
        $repo = $this->get('doctrine.odm.mongodb.document_manager')->getRepository('AyamelResourceBundle:Resource');
		$resource = $repo->find($id);
		
		//throw not found exception if necessary
		if(!$resource) {
			throw $this->createNotFoundException("Object with requested ID does not exist.");
		}
		
		//assemble final content structure
		$content = array(
			'meta' => array(
				'code' => '200',
				'time' => time(),
			),
			'resource' => print_r($resource, true),		//a quick hack, it won't be done this way for real
		);
		
		//encode the structure
		$content = json_encode($content);

		//build response
		$response = new Response();
		$response->headers->set('content-type', 'application/json');
		$resonse->setCode(200);
		$resonse->setContent($content);
		
		//allow public objects to be cached for a small amount of time
		if($resource->getPublic()) {
			$response->setPublic();
			$response->setMaxAge($this->container->getParameter('ayamel.api.public_object_cache_age', 300));
		}

		return $response;
	}
	
}