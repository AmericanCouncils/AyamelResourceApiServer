<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
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
    
    /**
     * The fields listed in this array cannot be set by a client, they may only be set the server.
     *
     * @var array
     */
    protected $properyBlacklist = array(
        'id',
        'date_added',
        'contributer',
        'contributer_name'
    );
	
	public function executeAction(Request $request) {
		
		//if we can't decode, or interpret as a query string, then it's a malformed request
        $data = $this->decodeIncomingData($request->getContent());
		
		//make sure any blacklisted properties are not set
        $this->validateBlackListedProperties($data);
        
		//build a new resource instance
		$resource = $this->createNewResourceFromArray($data);
		
		//set the properties controlled by the resource library
		$resource->setDateAdded(time());
		
        //attempt to save
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
		
		//TODO: properly generate an upload token
		$uploadToken = '97asdf_place_holder_upload_token_jlkj3433';
		
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

    protected function validateBlackListedProperties(array $data) {
        $badFields = array();
        foreach($this->properyBlacklist as $prop) {
            if(isset($data[$prop])) $badFields[] = $prop;
        }
        
        if(!empty($badFields)) {
			throw $this->createHttpException(400, sprintf("The following fields cannot be set by the client: %s", implode(", ", $badFields)));
        }
    }

    protected function decodeIncomingData($string) {
		if(!$data = @json_decode($string, true)) {
            parse_str($string, $data);

            if(empty($data) || !is_array($data)) {
    			throw $this->createHttpException(400, "Data structure could not be parsed.  Make sure you are sending valid JSON or a properly formatted query string.");
            }
		}
        
        return $data;
    }
    
    protected function createNewResourceFromArray(array $data) {
        //TODO: will need to flesh this out considerably, this is a quick hack
		// - special validation for certain fields is required... build a ResourceValidator class for this
		// - eventually do this:
		/*
			try {
				$this->container->get('ayamel.resource.data_validator')->validateClientData($data);
			} catch (\Exception $e) {
				throw $this->createHttpException(400, $e->getMessage());
			}
		*/

        $resource = new Resource;
        
		//assign received data
		foreach($data as $key => $val) {
			//derive method name
			$n = explode("_", $key);

			array_walk($n, function($item){
                return ucfirst(strtolower($item));
            });
            
			$method = 'set'.implode("", $n);
			if(method_exists($resource, $method)) {
				$resource->$method($val);
			} else {
				throw $this->createHttpException(400, "Tried setting a non-existing field ($key)");
			}
		}
        
        return $resource;
    }
}