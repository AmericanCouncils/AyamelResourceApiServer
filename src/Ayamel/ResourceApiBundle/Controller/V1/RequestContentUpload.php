<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class RequestContentUpload extends ApiController {
    
    public function executeAction($id) {
        throw $this->createHttpException(501);
    }
    
}
