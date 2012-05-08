<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Receives a query parameter for 'uri', and tries to derive a full resource structure from it.
 *
 * @author Evan Villemez
 */
class ScanResourceUri extends ApiController {
    
    public function executeAction(Request $request) {
        
        //get the uri
        $uri = urldecode($request->query->get('uri', false));

        if(!$uri) {
            throw $this->createHttpException("400", "The [uri] query parameter was not provided.");
        }
        
        //general format check
        $exp = explode("://", $uri);
        if(2 !== count($exp)) {
            throw $this->createHttpException(400, "The uri was not in the expected [scheme://path] format.");
        }
        
        $scheme = $exp[0];
        $path = $exp[1];
        $provider = $this->container->get('ayamel.resource.provider');
        
        //check scheme
        if(!$provider->handlesScheme($scheme)) {
            throw $this->createHttpException(422, sprintf("Cannot interpret resources via scheme [%s]", $scheme));
        }
        
        //create a resource
        $resource = $provider->createResourceFromUri($uri);
        
        //or not
        if(!$resource instanceof Resource) {
            throw $this->createHttpException(422, "Could not derive a valid resource from the given uri.");
        }
        
        //return it
        return array(
            'response' => array(
                'code' => 203,
            ),
            'resource' => $resource
        );
    }

}
