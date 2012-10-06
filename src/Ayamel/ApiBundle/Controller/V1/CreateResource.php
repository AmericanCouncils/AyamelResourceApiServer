<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\Client;
use Ayamel\ResourceBundle\Document\Origin;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Accepts data from a request object, attempting to build and save a new resource object.
 *
 * @author Evan Villemez
 */
class CreateResource extends ApiController {
    
    /**
     * Accepts data from a request object, attempting to build and save a new resource object.
     * 
     * @ApiDoc(
     *      resource=true,
     *      description="Create a resource",
     *      input="Ayamel\ResourceBundle\Document\Resource",
     *      return="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
     * );
     *
     * @param Request $request 
     */
	public function executeAction(Request $request) {
		
        /*
		//get validator
		$validator = $this->container->get('ayamel.api.client_data_validator');
		
		//decode incoming data
		$data = $validator->decodeIncomingResourceDataByRequest($request);
		
		//build a new resource instance based on received data
		$resource = $validator->createAndValidateNewResource($data);
        */
        
        //create object from client request
        $resource = $this->container->get('ac.webservices.validator')->createObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $this->getRequest());
		
		//set the properties controlled by the resource library
		$resource->setStatus(Resource::STATUS_AWAITING_CONTENT);
        
        //fill in client info
        if(!isset($resource->client)) {
            $resource->setClient(new Client);
        }
        if(!$resource->client->getId()) {
            $request::trustProxyData();
            $resource->client->setId($request->getClientIp());
        }
		
        //attempt to persisting the object, most likely to mongo
		try {
            $this->container->get('ayamel.resource.manager')->persistResource($resource);
		} catch(\Exception $e) {
			throw $this->createHttpException(400, $e->getMessage());
		}
		
        //generate an upload token
		$newID = $resource->getId();
		$uploadToken = $this->container->get('ayamel.api.upload_token_manager')->createTokenForId($newID);
		
        //notify rest of system of new resource
        $this->container->get('ayamel.api.dispatcher')->dispatch(Events::RESOURCE_CREATED, new ApiEvent($resource));
        
        //define returned content structure
        $content = array(
            'response' => array(
                'code' => 201,
            ),
			'content_upload_url' => $this->container->get('router')->generate('AyamelApiBundle_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true),
            'resource' => $resource
        );
        
		return $content;
	}

}
