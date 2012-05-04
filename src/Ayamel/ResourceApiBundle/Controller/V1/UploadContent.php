<?php

namespace Ayamel\ResourceApiBundle\Controller\V1;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Ayamel\ResourceApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ResourceApiBundle\Event\HandleUploadedContentEvent;

/**
 * Receives, validates and process content uploads for a resource object.
 *
 * @author Evan Villemez
 */
class UploadContent extends ApiController {
    
    public function executeAction($id, $token) {
        throw $this->createHttpException(501);
        
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
        
        //get the api event dispatcher
        $apiDispatcher = $this->container->get('ayamel.api.dispatcher');
        
        //notify system to resolve uploaded content from the request
        $request = $this->getRequest();
        $event = $apiDispatcher->dispatch(Events::RESOLVE_UPLOADED_CONTENT, new ResolveUploadedContentEvent($resource, $request));
        $contentType = $event->getContentType();
        $contentData = $event->getContentData();
        
        //if we weren't able to resolve incoming content, it must be a bad request
        if(false === $contentData) {
            throw $this->createHttpException(400, "Could not resolve valid content.");
        }
        
        //notify system to handle uploaded content however is necessary and modify the resource accordingly
        $event = $apiDispatcher->dispatch(Events::HANDLE_UPLOADED_CONTENT, new HandleUploadedContentEvent($resource, $contentType, $contentData));
        
        //persist the resource, as it may have changed
        $resource = $event->getResource();
        $this->container->get('ayamel.resource.manager')->persistResource($resource);
        
        //notify the system that a resource has changed
        $apiDispatcher->dispatch(Events::RESOURCE_MODIFIED, new ApiEvent($resource));
        
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