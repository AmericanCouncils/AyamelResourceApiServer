<?php
namespace Ayamel\ResourceApiBundle\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\ResourceDocumentsFactory;

/**
 * A class with convenience methods for decoding and validating incoming API data for create and update actions.
 *
 * @author Evan Villemez
 */
class ClientResourceDataValidator {
	
	/**
	 * Fields allowed to be set by the client during creation.
	 *
	 * @var array
	 */
	protected $creationFieldWhitelist = array(
		'title',
		'description',
		'public',
		'relations',
		'l2Data',
		'copyright',
		'keywords',
		'categories',
	);
	
	/**
	 * Fields allowed to be set by the client during update operations.
	 *
	 * @var array
	 */
	protected $updateFieldWhitelist = array(
		'title',
		'description',
		'public',
		'relations',
		'l2Data',
		'copyright',
		'keywords',
		'categories',
	);
	
	/**
	 * Construct allows modifying default field whitelists used during create and update.
	 *
	 * @param array $creationFieldWhitelist 
	 * @param array $updateFieldWhitelist 
	 */
	public function __construct(array $creationFieldWhitelist = null, array $updateFieldWhitelist = null) {
		if($creationFieldWhitelist) {
			$this->creationFieldWhitelist = $creationFieldWhitelist;
		}
		
		if($updateFieldWhitelist) {
			$this->updateFieldWhitelist = $updateFieldWhitelist;
		}
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
	
	/**
	 * Validate and create new resource from user provided data structure.
	 *
	 * @param array $data 
	 * @return Ayamel\ResourceBundle\Document\Resource
	 */
	public function createAndValidateNewResource($data = array()) {
		$this->scanWhitelistField(array_flip($this->creationFieldWhitelist), $data);
		
		//try using the factory to create the resource with the given data
		try {
			return ResourceDocumentsFactory::createResourceFromArray($data);
		} catch (\Exception $e) {
			throw new HttpException(400, $e->getMessage());
		}
	}

	/**
	 * Make sure data is in array of fields allowed to be set.
	 *
	 * @param array $whiteList 
	 * @param array $data 
	 * @throws HttpException(400) when disallowed fields are encountered
	 */
	protected function scanWhitelistField(array $whiteList, array $data) {
        $badFields = array();
        foreach($data as $key => $val) {
            if(!isset($whiteList[$key])) $badFields[] = $key;
        }
        
        if(!empty($badFields)) {
			throw new HttpException(400, sprintf("The following fields cannot be set by the client: %s", implode(", ", $badFields)));
        }
	
	}
	
	/**
	 * Validate and modify resource from user provided data structure.
	 *
	 * @param Ayamel\ResourceBundle\Document\Resource $resource 
	 * @param array $data 
	 * @return Ayamel\ResourceBundle\Document\Resource
	 */
	public function modifyAndValidateExistingResource(Resource $resource, $data = array()) {
		$this->scanWhitelistField(array_flip($this->updateFieldWhitelist), $data);
		
		try {
			ResourceDocumentsFactory::callSetters($resource, $data);
			return $resource;
		} catch (\Exception $e) {
			throw new HttpException(400, $e->getMessage());
		}
	}
	
}