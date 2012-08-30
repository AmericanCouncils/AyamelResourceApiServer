<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class GetResource extends ApiController {
    
    /**
     * Returns a resources object structure by its ID.
     *
     * @param string $id The id of the object to retrieve.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Return a resource",
     *      return="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
     * );
     *
     */
    public function executeAction($id) {
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
                
        //check for deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        //assemble final content structure
        $content = array(
            'response' => array(
                'code' => 200,
            ),
            'resource' => $resource,
        );
        
        return $content;
        //return \FOS\RestBundle\View::create($content, $httpStatusCode);
    }
}
