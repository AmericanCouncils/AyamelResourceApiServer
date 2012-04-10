<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class EmbedPlayer extends ApiController {
	
	public function executeAction($id, $token) {
		throw $this->createHttpException(501);		
	}

}
