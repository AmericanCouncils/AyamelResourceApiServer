<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;

class ScanResourceUri extends ApiController {
    
    public function executeAction($uri) {
        throw $this->createHttpException(501);
        
        $resource = $this->container->get('ayamel.resource.provider')->createResourceFromUri(urldecode($uri));
        
        if(!$resource instanceof Resource) {
            throw $this->createHttpException(400, "Could not derive a valid resource from the given uri.");
        }
        
        return array(
            'response' => array(
                'code' => 200,
            ),
            'resource' => $resource
        );
    }
    
}
