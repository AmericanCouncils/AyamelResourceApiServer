<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class Search extends ApiController {
    
    public function executeAction() {
        throw $this->createHttpException(501);
    }
    
}
