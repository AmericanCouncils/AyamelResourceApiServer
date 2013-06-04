<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class GetResource extends ApiController
{

    /**
     * Returns a resources object structure by its ID. By default this will also return Relations created by the Resource owner and
     * requesting client.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Return a resource",
     *      output="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of yml or json"},
     *          {"name"="relations", "default"="true", "description"="Whether or not to return relations created by the owner and requesting client."}
     *      }
     * );
     *
     * @param string $id The id of the object to retrieve.
     */
    public function executeAction($id)
    {
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        $request = $this->getRequest();

        //check for deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        //add relations default relations, unless told not to
        if ($request->get('relations', null) !== 'false') {

            //TODO: get relevant relations
            //by default limited to the relations created by the requesting client, and the owner
            //of the original resource
            $relations = $this->getRepo('Ayamel\ResourceBundle\Document\Relation')->getRelationsForResource($id);
            if ($relations) {
                $resource->setRelations(iterator_to_array($relations));
            }
        }

        //return service response
        return $this->createServiceResponse(array('resource' => $resource), 200);
    }
}
