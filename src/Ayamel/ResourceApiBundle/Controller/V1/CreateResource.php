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
		
		//get validator
		$validator = $this->container->get('ayamel.api.client_data_validator');
		
		//decode incoming data
		$data = $validator->decodeIncomingResourceDataByRequest($request);
		
		//build a new resource instance based on received data
		$resource = $validator->createAndValidateNewResource($data);
		
		//set the properties controlled by the resource library
		$time = time();
		$resource->setDateAdded($time);
		$resource->setDateModified($time);
		
        //attempt to persist object to Mongo
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
		
		//TODO: properly generate and store an upload token
		$newID = $resource->getId();
		$uploadToken = '97asdf_place_holder_upload_token_jlkj3433';
		
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