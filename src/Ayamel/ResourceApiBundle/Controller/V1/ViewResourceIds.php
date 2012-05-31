<?php
namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns a resources object structure by its ID.
 */
class ViewResourceIds extends ApiController {
    
    public function executeAction() {
        
		$mongo = $this->container->get('doctrine.odm.mongodb.default_connection');
        
        $ids = array();
        foreach($mongo->ayamel->resources->find(array(), array('id' => 1)) as $key => $val) {
            $ids[] = $key;
        }
        
        //assemble final content structure
        $content = array(
            'response' => array(
                'code' => 200,
            ),
            'ids' => $ids,
        );
        
        return $content;
        //return \FOS\RestBundle\View::create($content, $httpStatusCode);
    }
}
