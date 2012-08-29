<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Returns a resources object structure by its ID.
 */
class ViewResourceIds extends ApiController {
    
    /**
     * Returns a list of all resource IDs available in the system.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="View available IDs"
     * );
     * 
     */
    public function executeAction() {
        
		$mongo = $this->container->get('doctrine_mongodb.odm.default_connection');
        
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
