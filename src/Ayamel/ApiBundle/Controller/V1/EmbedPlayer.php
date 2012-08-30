<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;

class EmbedPlayer extends ApiController {
    
    public function executeAction($id, $token) {
        throw $this->createHttpException(501);      
    }

}
