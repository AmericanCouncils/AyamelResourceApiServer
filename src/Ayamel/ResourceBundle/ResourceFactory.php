<?php

namespace Ayamel\ResourceBundle;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;

class ResourceFactory {

	static public function createResourceFromArray(array $data) {
		$resource = new Resource;

		self::callSetters($resource, $data);

		return $resource;
	}
	
	static public function createRelationFromArray(array $data) {
		
	}
	
	static public function createFileReferenceFromArray(array $data) {
		
	}
	
	static public function callSetters($object, $data) {
		//assign received data
		foreach($data as $key => $val) {
			//derive method name
			$n = explode("_", $key);

			array_walk($n, function($item){
                return ucfirst(strtolower($item));
            });
            
			$method = 'set'.implode("", $n);
			if(method_exists($object, $method)) {
				$object->$method($val);
			} else {
				throw new \RuntimeException("Tried setting a non-existing field ($key)");
			}
		}
		
	}
	
}