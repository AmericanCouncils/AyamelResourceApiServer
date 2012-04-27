<?php
namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;

/**
 * Accepts data from a request object, attempting to build and save a new resource object.
 *
 * @author Evan Villemez
 */
class CreateResource extends ApiController {
    	
	public function executeAction(Request $request) {
		
		//TODO: check authorization depending on how it's implemented
		
		//get validator
		$validator = $this->container->get('ayamel.api.client_data_validator');
		
		//decode incoming data
		$data = $validator->decodeIncomingResourceDataByRequest($request);
		
		//build a new resource instance based on received data
		$resource = $validator->createAndValidateNewResource($data);
		
		//set the properties controlled by the resource library
		$date = new \DateTime();
		$resource->setDateAdded($date);
		$resource->setDateModified($date);
		$resource->setStatus(Resource::STATUS_AWAITING_CONTENT);
		
        //attempt to persist object to Mongo
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
		try {
	        $dm->persist($resource);
	        $dm->flush();
		} catch(\Exception $e) {
			throw $this->createHttpException(400, $e->getMessage());
		}
		
		$newID = $resource->getId();
		$uploadToken = $this->container->get('ayamel.api.upload_token_manager')->createTokenForId($newID);
		
        //define returned content structure
        $content = array(
            'response' => array(
                'code' => 201,
            ),
			'content_upload_url' => $this->container->get('router')->generate('AyamelResourceApiBundle_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true),
            'resource' => $resource
        );
        
		return $content;
	}

}