<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResourceEvent;
use Symfony\Component\HttpFoundation\Request;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Client;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CreateResource extends ApiController
{
    /**
     * Accepts data from a request object, attempting to build and save a new resource object.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Create a resource",
     *      input="Ayamel\ResourceBundle\Document\Resource",
     *      output="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *      }
     * );
     *
     * @param Request $request
     */
    public function executeAction(Request $request)
    {
        $this->requireAuthentication();

        //create object from client request
        $resource = $this->container->get('ac.webservices.object_validator')->createObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $this->getRequest());

        //set the properties controlled by the resource library
        $resource->setStatus(Resource::STATUS_AWAITING_CONTENT);

        //fill in client info
        $clientDoc = $this->getApiClient()->createClientDocument();
        $resource->setClient($clientDoc);

        //attempt to validate and persist the object
        $this->validateObject($resource);
        $manager = $this->get('doctrine_mongodb')->getManager();
        try {
            $manager->persist($resource);
            $manager->flush();
        } catch (\Exception $e) {
            throw $this->createHttpException(400, $e->getMessage());
        }

        //generate an upload token
        $newID = $resource->getId();
        $uploadToken = $this->container->get('ayamel.api.upload_token_manager')->createTokenForId($newID);

        //notify rest of system of new resource
        $this->container->get('event_dispatcher')->dispatch(Events::RESOURCE_CREATED, new ResourceEvent($resource));

        $uploadUrl = $this->container->get('router')->generate('api_v1_upload_content', array('id' => $resource->getId(), 'token' => $uploadToken), true);

        //define returned content structure
        return $this->createServiceResponse(array(
            'contentUploadUrl' => $uploadUrl,
            'resource' => $resource
        ), 201);
    }

}
