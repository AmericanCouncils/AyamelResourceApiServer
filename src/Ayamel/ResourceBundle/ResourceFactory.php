<?php

namespace Ayamel\ResourceBundle;

class ResourceFactory {

	static public function createResourceFromArray(array $data) {
		
	}
	
	static public function createRelationFromArray(array $data) {
	
	}
	
	static public function createFileReferenceFromArray(array $data) {
	
	}
	
	static protected function callSetters($object, $array) {
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
				throw \RuntimeException("Tried setting a non-existing field ($key)");
			}
		}
		
	}
	
}