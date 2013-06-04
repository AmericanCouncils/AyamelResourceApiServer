<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ModifyResource extends ApiController
{
    /**
     * Accepts data from a request object, attempting to modify a specific resource object.  If you want to remove
     * a field value, you can do that by setting its value to null.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Modify a resource",
     *      output="Ayamel\ResourceBundle\Document\Resource",
     *      input="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "dataType"="string", "default"="json", "description"="Return format, can be one of xml, yml or json"}
     *      }
     * );
     *
     * @param string $id
     */
    public function executeAction($id)
    {
        //get the resource
        $resource = $this->getRequestedResourceById($id);

        //check for deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        //use object validation service to modify the existing object
        $modifiedResource = $this->container->get('ac.webservices.object_validator')->modifyObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $this->getRequest(), $resource);

        //save it
        try {
            $manager = $this->get('doctrine_mongodb')->getManager();
            $manager->flush();
        } catch (\Exception $e) {
            throw $this->createHttpException(400, $e->getMessage());
        }

        //notify rest of system of modified resource
        $event = new ApiEvent($modifiedResource);
        $this->container->get('ayamel.api.dispatcher')->dispatch(Events::RESOURCE_MODIFIED, $event);

        //return it
        return $this->createServiceResponse(array('resource' => $modifiedResource), 200);
    }

}
