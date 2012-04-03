<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns a resources object structure by its ID.
 */
class GetResource extends ApiController {
	
	public function executeAction($id) {

		//get repository and find requested object
        $repo = $this->container->get('doctrine.odm.mongodb.document_manager')->getRepository('AyamelResourceBundle:Resource');
		$resource = $repo->find($id);

		//throw not found exception if necessary
		if(!$resource) {
			throw $this->createHttpException(404);
		}
		
		//throw access denied exception if resource isn't public and client doesn't own it
		if(!$resource->getPublic()) {
			if($this->getApiClient()->getName() !== $resource->getContributer()) {
				throw $this->createHttpException(403);
			}
		}
		
		//assemble final content structure
		$content = array(
			'meta' => array(
				'code' => '200',
				'time' => time(),
			),
			'resource' => $resource,
		);
		
//		return $content;
		
		/*
            Eventually we'll just run the content structure here, and let the ApiWorkflowSubscriber::onKernelView() listener figure out which format to return.
		*/
		
		//encode the structure
		$content = $this->container->get('serializer')->serialize($content, 'json');

		//build response
		$response = new Response();
		$response->headers->set('content-type', 'application/json');
		$response->setStatusCode(200);
		$response->setContent($content);
		
		//allow public objects to be cached for a small amount of time
		if($resource->getPublic()) {
			$response->setPublic();
//			$response->setMaxAge(10);
		}

		return $response;
	}
}