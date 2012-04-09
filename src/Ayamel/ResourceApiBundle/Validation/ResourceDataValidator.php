<?php
namespace Ayamel\ResourceApiBundle\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\ResourceFactory;

/**
 * A class with convenience methods for decoding and validating incoming API data for create and update actions.
 *
 * @author Evan Villemez
 */
class ResourceDataValidator {
	
	/**
	 * Fields allowed to be set by the client during creation.
	 *
	 * @var array
	 */
	protected $creationFieldWhitelist = array(
		'title',
		'description',
		
	);
	
	
	protected $updateFieldWhitelist = array(
		
	);
	
	public function __construct($creationFieldWhitelist = array(), $updateFieldWhitelist = array()) {
		
	}
	
	/**
	 * Decode incoming data structure, given a request object.
	 *
	 * @param Request $request 
	 * @throws Symfony\Component\HttpKernel\Exception\HttpException(400) when data cannot be parsed
	 * @return array
	 */
	public function decodeIncomingResourceDataByRequest(Request $request) {
		$string = $request->getContent();
		
		//attempt to JSON decode first
		if(!$data = @json_decode($string, true)) {
			
			//otherwise attempt to parse as a query string
            parse_str($string, $data);

            if(empty($data) || !is_array($data)) {
    			throw new HttpException(400, "Data structure could not be parsed.  Make sure you are sending valid JSON or a properly formatted query string.");
            }
		}
        
        return $data;
	}
	
	public function createAndValidateNewResource($data = array()) {
		$this->scanFields(array_flip($this->creationFieldsWhitelist), $data);
		
		//try using the factory to create the resource with the given data
		try {
			return ResourceFactory::createResourceFromArray($data);
		} catch (\Exception $e) {
			throw new HttpException(400, $e->getMessage());
		}
	}

	protected function scanWhitelistFields(array $whiteList, array $data) {
        $badFields = array();
        foreach($data as $prop) {
            if(!isset($whitelist[$prop])) $badFields[] = $prop;
        }
        
        if(!empty($badFields)) {
			throw new HttpException(400, sprintf("The following fields cannot be set by the client: %s", implode(", ", $badFields)));
        }
	
	}
	
	
	public function modifyAndValidateExistingResource(Resource $resource, $data = array()) {
		
	}
	
}