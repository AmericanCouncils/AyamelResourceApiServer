<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Removes a Resource object by it's ID.
 *
 * @author Evan Villemez
 */
class DeleteResource extends ApiController
{
    /**
     * Removes a Resource object by it's ID.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Remove resource.",
     *      return="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
     * )
     *
     * @param string $id
     */
    public function executeAction($id)
    {
        //get the resource
        $resource = $this->getRequestedResourceById($id);

        //check for already deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        $apiDispatcher = $this->container->get('ayamel.api.dispatcher');

        //notify system to remove content for resource
        $apiDispatcher->dispatch(Events::REMOVE_RESOURCE_CONTENT, new ApiEvent($resource));

        //remove from storage (sort of), just clears data and marks as deleted
        $manager = $this->getDocManager();
        $resource = $manager->getRepository('AyamelResourceBundle:Resource')->deleteResource($resource);

        //delete all relations for this resource
        $this->getRepo('AyamelResourceBundle:Relation')->deleteRelationsForResource($resource->getId());

        //notify rest of system of deleted resource
        $apiDispatcher->dispatch(Events::RESOURCE_DELETED, new ApiEvent($resource));

        //return ok
        return $this->createServiceResponse(array('resource' => $resource), 200);
    }

}
