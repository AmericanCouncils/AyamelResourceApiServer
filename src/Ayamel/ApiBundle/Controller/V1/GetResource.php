<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class GetResource extends ApiController
{

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
     *          {"name"="relations", "default"="true", "description"="Whether or not to return relations created by the owner and requesting client."},
     *      }
     * );
     *
     */
    public function executeAction($id)
    {
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        $request = $this->get('request');
        
        //check for deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        if (!$request->get('relations', false)) {
            //TODO: get relevant relations

            //by default limited to the relations created by the requesting client, and the owner
            //of the original resource
        }

        //return service response
        return $this->createServiceResponse(array('resource' => $resource), 200);
    }
}
