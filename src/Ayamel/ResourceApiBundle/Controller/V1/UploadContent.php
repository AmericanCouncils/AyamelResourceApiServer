<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class UploadContent extends ApiController {
	
	public function executeAction($id, $token) {
		throw $this->createHttpException(501);
		
		//use code 202
	}

}
