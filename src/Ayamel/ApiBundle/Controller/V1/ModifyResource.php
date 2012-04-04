<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;

class ModifyResource extends ApiController {
	
	public function executeAction($id) {
		throw $this->createHttpException(501);
	}
	
}
