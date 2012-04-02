<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;

class ScanResourceUri extends ApiController {
	
	public function executeAction($uri) {
		throw new \RuntimeException(__METHOD__." not yet implemented.");
		
		$resource = $this->get('ayamel_resource_scanner')->deriveResourceFromUri(urldecode($uri));
		
		if($resource instanceof Resource) {
		
		} else {
			//figure out a default...
		}
		
	}
	
}
