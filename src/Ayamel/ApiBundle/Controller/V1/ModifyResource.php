<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResourceEvent;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use AC\WebServicesBundle\Serializer\DeserializationContext;

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
        $this->requireAuthentication();

        //get the resource
        $resource = $this->getRequestedResourceById($id);

        //check for deleted resource
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        $this->requireResourceOwner($resource);

        //use object validation service to modify the existing object
        $ctx = DeserializationContext::create()->setTarget($resource);
        $modifiedResource = $this->decodeRequest('Ayamel\ResourceBundle\Document\Resource', $ctx);
        $this->validateObject($modifiedResource);

        //save it
        try {
            $manager = $this->get('doctrine_mongodb')->getManager();
            $manager->flush();
        } catch (\Exception $e) {
            throw $this->createHttpException(400, $e->getMessage());
        }

        //notify rest of system of modified resource
        $this->container->get('event_dispatcher')->dispatch(Events::RESOURCE_MODIFIED, new ResourceEvent($modifiedResource));

        //return it
        return $this->createServiceResponse(array('resource' => $modifiedResource), 200);
    }

}
