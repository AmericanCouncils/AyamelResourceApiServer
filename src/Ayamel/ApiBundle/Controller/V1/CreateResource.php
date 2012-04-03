<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;

class CreateResource extends ApiController {
	
	public function executeAction(Request $request) {
		
		//if we can't decode, malformed request
		if(!$data = json_decode($request->getContent())) {
			throw $this->createHttpException(400, "Data structure could not be parsed.");
		}
		
		//make sure 'id' property is not set
		if(isset($data['id'])) {
			throw $this->createHttpException(400, "Cannot set the id property for a new resource.");
		}
		
		//build a new resource
		$resource = new Resource;
		
		//assign received data
        //TODO: will need to flesh this out considerably, this is a quick hack
		foreach($data as $key => $val) {
			//derive method name
			$n = explode("_", $key);
			array_walk($n, 'ucfirst');
			$method = 'set'.implode("", $n);
			
			$resource->$method($val);
		}
		
        //attempt to create/save
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
		
        //define returned content structure
        $content = array(
            'meta' => array(
                'code' => '201',
                'time' => time(),
            ),
            'resource' => $resource
        );
        
        //convert to json
        $content = $this->container->get('serialize')->serialize($content, 'json');
        
        //build & return response
        $response = new Response($content, 201, array('content-type' => 'application/json'));

        return $response;
	}
}