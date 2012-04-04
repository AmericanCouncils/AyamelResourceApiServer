<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;

class Search extends ApiController {
	
	public function executeAction() {
		throw $this->createHttpException(501);
	}
	
}
