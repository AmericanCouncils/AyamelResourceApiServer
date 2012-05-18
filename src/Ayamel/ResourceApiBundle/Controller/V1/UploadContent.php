<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Ayamel\ResourceApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ResourceApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Receives, validates and process content uploads for a resource object.
 *
 * @author Evan Villemez
 */
class UploadContent extends ApiController {
    
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
        
        //TODO: check for (cache) resource lock, throw 423
        //TODO: lock resource
        
        //get the api event dispatcher
        $apiDispatcher = $this->container->get('ayamel.api.dispatcher');

        //notify system to resolve uploaded content from the request
        $request = $this->getRequest();
        try {
            $event = $apiDispatcher->dispatch(Events::RESOLVE_UPLOADED_CONTENT, new ResolveUploadedContentEvent($resource, $request));
        } catch (\Exception $e) {
            //TODO: unlock resource
            throw ($e instanceof HttpException) ? $e : $this->createHttpException(500, $e->getMessage());
        }
        $contentType = $event->getContentType();
        $contentData = $event->getContentData();
        
        //if we weren't able to resolve incoming content, it must be a bad request
        if(false === $contentData) {
            //TODO: unlock resource
            throw $this->createHttpException(422, "Could not resolve valid content.");
        }        
        
        //notify system to handle uploaded content however is necessary and modify the resource accordingly
        try {
            //remove old resource content
            $apiDispatcher->dispatch(Events::REMOVE_RESOURCE_CONTENT, new ApiEvent($resource));
            
            $event = $apiDispatcher->dispatch(Events::HANDLE_UPLOADED_CONTENT, new HandleUploadedContentEvent($resource, $contentType, $contentData));
        } catch (\Exception $e) {
            //TODO: unlock resource
            throw ($e instanceof HttpException) ? $e : $this->createHttpException(500);
        }
        
        //if resource was processed, persist it and notify the system that a resource has changed
        if ($event->isResourceModified()) {
            try {
                //persist it
                $resource = $event->getResource();
                $resource->setStatus(Resource::STATUS_AWAITING_PROCESSING);
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
                'code' => 202
            ),
            'resource' => $resource
        );
    }
}
