<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ApiBundle\Controller\ApiController;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Receives, validates and process content uploads for a resource object.
 *
 * @author Evan Villemez
 */
class UploadContent extends ApiController {
    
    /**
     * Upload content for a resource object.  Note that an upload URL is a one-time-use url.  If uploading content fails
     * for any reason, you must request a new upload url to try again.  The reason for this is that the upload
     * url may or may not handle content directly from an authorized client.  Technically files can be uploaded directly
     * from a user of a client system in order to avoid having to send a file via multiple servers.  Because of this, the library
     * will allow clients to reserve one-time-use urls for sending content, which they can then expose to their internal users
     * as nedded.  
     *
     * Content can be provided in one of many formats, refer to the list below:
     *
     * -    Upload a file to be stored by the Ayamel server by providing a file upload via the `file` post field. 
     *      Files uploaded in this manner will be automatically scheduled to be transcoded into other web-accessible
     *      formats, if applicable. (Not implemented yet)
     *
     * -    Specify a reference to an original file via a public URI, this can be done via the `uri` post field, or 
     *      by passing a JSON object with the `uri` key.  The specified uri will be processed to check for availability.
     *      If the uri is in a custom format known to the Ayamel Resource Library, other resource information may be derived and 
     *      added into the resource.
     * 
     *          {
     *              "uri": "http://example.com/files/my_video.wmv"
     *          }
     *
     *      You send custom URI's for special providers as well:
     *
     *          {
     *              "uri": "youtube://txqiwrbYGrs"
     *          }
     *
     * -    Specify an array of file references on a remote file server by passing a JSON object with the `remoteFiles` key 
     *      containing an array of file objects.  These references are stored exactly as received.
     * 
     *          {
     *              "remoteFiles": [
     *                  {
     *                      "downloadUri": "http://example.com/files/some_video_original.wmv",
     *                      "mime": "video/x-ms-wmv",
     *                      "representation": "original;0",
     *                      "attributes": {
     *                          "bytes": 14658,
     *                          "duration": 300,
     *                          "frameSize": {"width":720,"height":480},
     *                          "frameRate": 48,
     *                          "bitrate": 44000,
     *                      }
     *                   },
     *                   {
     *                      "downloadUri": "http://example.com/files/transcoded.mp4",
     *                      "mime": "video/mp4",
     *                      "representation": "transcoded;1",
     *                      "attributes": {
     *                          "bytes": 9600,
     *                          "duration": 300,
     *                          "frameSize": {"width":720,"height":480},
     *                          "frameRate": 48,
     *                          "bitrate": 36000,
     *                      }
     *                   }
     *              ]
     *          }
     *
     * 
     * @ApiDoc(
     *      resource=true,
     *      description="Upload resource content.",
     *      return="Ayamel\ResourceBundle\Document\Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *          {"name"="replace", "dataType"="boolean", "description"="If true, will delete any previous content associated with the resource before adding new content.", "default"=true}
     *      }
     * )
     * 
     * @param string $id 
     * @param string $token 
     */
    public function executeAction($id, $token) {
        //get the resource
        $resource = $this->getRequestedResourceById($id);
                
        //check for deleted resource
        if(null != $resource->getDateDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        //get the upload token manager
        $tm = $this->container->get('ayamel.api.upload_token_manager');

        //use the upload token, if using the token fails, 401
        try {
            $tm->useTokenForId($id, $token);
        } catch (\Exception $e) {
            $tm->removeTokenForId($id);
            throw $this->createHttpException(401, $e->getMessage());
        }
        $tm->removeTokenForId($id);
        
        //make sure the resource isn't currently being processed by something
        if(Resource::STATUS_PROCESSING === $resource->getStatus()) {
            throw $this->createHttpException(423, "Resource content is currently being processed, try modifying the content later.");
        }
        
        $lockKey = $resource->getId()."_upload_lock";
        //TODO: check for (cached) resource lock, throw 423 if present
        //TODO: lock resource
        
        //get the api event dispatcher
        $apiDispatcher = $this->container->get('ayamel.api.dispatcher');

        //notify system to resolve uploaded content from the request
        $request = $this->getRequest();
        
        //determine whether or not to remove previous resource content
        $removePreviousContent = ('true' === $request->query->get('replace', 'true'));
        
        try {
            //dispatch the resolve event
            $resolveEvent = $apiDispatcher->dispatch(Events::RESOLVE_UPLOADED_CONTENT, new ResolveUploadedContentEvent($resource, $request, $removePreviousContent));
        } catch (\Exception $e) {
            //TODO: unlock resource
            throw ($e instanceof HttpException) ? $e : $this->createHttpException(500, $e->getMessage());
        }
        $contentType = $resolveEvent->getContentType();
        $contentData = $resolveEvent->getContentData();
        
        //if we weren't able to resolve incoming content, it must be a bad request
        if(false === $contentData) {
            //TODO: unlock resource
            throw $this->createHttpException(422, "Could not resolve valid content.");
        }
        
        //notify system to handle uploaded content however is necessary and modify the resource accordingly
        try {
            //notify system old content removal if necessary
            if($resolveEvent->getRemovePreviousContent()) {
                if(!isset($resource->content)) {
                    $resource->content = new ContentCollection;
                }
                $apiDispatcher->dispatch(Events::REMOVE_RESOURCE_CONTENT, new ApiEvent($resource));
                $resource->content = new ContentCollection;
            }
            
            $handleEvent = $apiDispatcher->dispatch(Events::HANDLE_UPLOADED_CONTENT, new HandleUploadedContentEvent($resource, $contentType, $contentData));
        } catch (\Exception $e) {
            //TODO: unlock resource
            throw ($e instanceof HttpException) ? $e : $this->createHttpException(500, $e->getMessage());
        }
        
        //if resource was processed, persist it and notify the system that a resource has changed
        if ($handleEvent->isResourceModified()) {
            try {
                //persist it
                $resource = $handleEvent->getResource();
                $this->container->get('ayamel.resource.manager')->persistResource($resource);
            
                //notify system
                $apiDispatcher->dispatch(Events::RESOURCE_MODIFIED, new ApiEvent($resource));
            } catch (\Exception $e) {
                //TODO: unlock resource
                throw $e;
            }
        } else {
            //TODO: unlock resource
            throw $this->createHttpException(422, "The content was not processed, thus the resource was not modified.");
        }
        
        //TODO: unlock resource
        
        //return 202 on success
        //TODO: return ServiceResponse::create(200, array('resource' => $resource));
        return array(
            'response' => array(
                'code' => ($resource->getStatus() === Resource::STATUS_NORMAL) ? 200 : 202,
            ),
            'resource' => $resource
        );
    }
}
