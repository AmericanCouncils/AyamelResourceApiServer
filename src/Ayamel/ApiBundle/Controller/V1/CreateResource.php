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
     * The fields listed in this query cannot be set by a client, they must be set the server.
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
				
        //attempt to save
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
        $content = $this->container->get('serializer')->serialize($content, 'json');
        
        //build & return response
        return new Response($content, 201, array('content-type' => 'application/json'));
	}

    protected function validateBlackListedProperties(array $data) {
        $badFields = array();
        foreach($this->blacklistProperties as $prop) {
            if(isset($data[$prop])) $badFields[] = $prop;
        }
        
        if(!empty($badFields)) {
			throw $this->createHttpException(400, sprintf("The following fields cannot be set by the client: %s", implode(", ", $badFields)));
        }
    }

    protected function decodeIncomingData($string) {
		if(!$data = @json_decode($string, true)) {
            parse_str($string, $data);

            if(empty($data)) {
    			throw $this->createHttpException(400, "Data structure could not be parsed.  Make sure you are sending valid json or a properly formatted query string.");
            }
		}
        
        return $data;
    }
    
    protected function createNewResourceFromArray(array $data) {
        //TODO: will need to flesh this out considerably, this is a quick hack

        $resource = new Resource;
        
		//assign received data
		foreach($data as $key => $val) {
			//derive method name
			$n = explode("_", $key);
//			array_walk($n, 'ucfirst');

			array_walk($n, function($item){
                return ucfirst(strtolower($item));
            });
            
			$method = 'set'.implode("", $n);
			
			$resource->$method($val);
		}
        
        return $resource;
    }
}