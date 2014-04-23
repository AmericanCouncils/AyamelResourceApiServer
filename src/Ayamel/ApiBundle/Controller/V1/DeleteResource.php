<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResourceEvent;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DeleteResource extends ApiController
{
    /**
     * Removes a Resource object by it's ID.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Remove resource.",
     *      output="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
     * )
     *
     * @param string $id
     */
    public function executeAction($id)
    {
        $this->requireAuthentication();

        //get the resource
        $resource = $this->getRequestedResourceById($id);

        //check for already deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        $this->requireResourceOwner($resource);

        $apiDispatcher = $this->container->get('event_dispatcher');

        //notify system to remove content for resource
        $apiDispatcher->dispatch(Events::REMOVE_RESOURCE_CONTENT, new ResourceEvent($resource));
        $manager = $this->getDocManager();


        //delete relations for resource
        $relations = $this->getRepo('AyamelResourceBundle:Relation')->getRelationsForResource($resource->getId());
        foreach ($relations as $relation) {
            $manager->remove($relation);
        }

        //remove from storage (sort of), just clears data and marks as deleted
        $resource = $this->getRepo('AyamelResourceBundle:Resource')->deleteResource($resource);

        //make a copy of the relations that are about to deleted
        $r = clone $relations;
        $r = $r->toArray();
        $manager->flush();

        //TODO: There's probably a better way to handle passing the deleted relations around
        //to the rest of the system than setting them on a deleted resource
        //...maybe the event should have separate relation-centric methods?

        //set relations on deleted resource, to pass around to subsystems
        $resource->setRelations($r);

        //notify rest of system of deleted resource
        $apiDispatcher->dispatch(Events::RESOURCE_DELETED, new ResourceEvent($resource));

        //unset deleted relations before returning response
        $resource->setRelations(null);

        //return ok
        return $this->createServiceResponse(array('resource' => $resource), 200);
    }
}
