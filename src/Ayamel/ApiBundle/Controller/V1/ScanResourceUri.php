<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ScanResourceUri extends ApiController
{
    /**
     * Derive as much of a full resource object as possible from a given URI. Currently supported URI schemes include:
     * 
     * - `http`/`https` - Points to any web-accessible file or webpage.  For example: `http://example.com/files/some_video.mp4`
     * - `youtube` - This designates a video resource from YouTube.  For example: `youtube://txqiwrbYGrs`
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Derive Resource from a URI.",
     *      output="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *          {"name"="uri", "required"=true, "dataType"="urlencoded string", "description"="The URI you want to scan as a Resource."}
     *      }
     * )
     *
     * @param Request $request
     */
    public function executeAction(Request $request)
    {
        //get the uri
        $uri = urldecode($request->query->get('uri', false));

        if (!$uri) {
            throw $this->createHttpException("400", "The [uri] query parameter was not provided.");
        }

        //general format check
        $exp = explode("://", $uri);
        if (2 !== count($exp)) {
            throw $this->createHttpException(400, "The URI was not in the expected [scheme://path] format.");
        }

        $scheme = $exp[0];
        $path = $exp[1];
        $provider = $this->container->get('ayamel.resource.provider');

        //check scheme
        if (!$provider->handlesScheme($scheme)) {
            throw $this->createHttpException(422, sprintf("Cannot interpret resources via scheme [%s]", $scheme));
        }

        //create a resource
        $resource = $provider->createResourceFromUri($uri);

        //or not
        if (!$resource instanceof Resource) {
            throw $this->createHttpException(422, "Could not derive a valid resource from the given uri.");
        }

        return $this->createServiceResponse(array('resource' => $resource), 203);
    }

}
