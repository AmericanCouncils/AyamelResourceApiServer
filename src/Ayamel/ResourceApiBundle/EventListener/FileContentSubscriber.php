<?php

namespace Ayamel\ResourceApiBundle\EventListener;

use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Ayamel\ResourceApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ResourceApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Registers API event listeners for managing the filesystem when certain actions occur.
 *
 * @author Evan Villemez
 */
class FileContentSubscriber implements EventSubscriberInterface {
	
    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    /**
     * Array of events subscribed to.
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        //TODO: consider setting the priorities for this to very low, in order to give other listeners a chance to handle specific file types earlier
        return array(
            Events::RESOURCE_DELETED => 'onResourceDeleted',
            Events::RESOLVE_UPLOADED_CONTENT => 'onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => 'onHandleContent',
        );
    }
    
    public function onResourceDeleted(ApiEvent $e) {
        $resource = $e->getResource();
        
        $this->container->get('ayamel.api.filesystem')->removeFilesForId($resource->getId());
    }
    
    /**
     * Check incoming request for an uploaded file.
     *
     * @param ResolveUploadedContentEvent $e 
     */
    public function onResolveContent(ResolveUploadedContentEvent $e) {
        $request = $e->getRequest();
        if($file = $request->files->get('file', false)) {
            $e->setContentType('file_upload');
            $e->setContentData($file);
        }
    }
    
    /**
     * Handle a file upload and modify resource accordingly.
     *
     * @param HandleUploadedContentEvent $e 
     */
    public function onHandleContent(HandleUploadedContentEvent $e) {
        if('file_upload' !== $e->getContentType()) {
            return;
        }
        
        $uploadedFile = $e->getContentData();        
        $resource = $e->getResource();
        $fs = $this->container->get('ayamel.api.filesystem');

        //TODO: figure out new name, preserving extension
        $newname = 'original';

        $fileRef = $fs->addFileForId($resource->getId(), FileReference::createFromLocalPath($file->getBasePath()), $newname, true);
        //TODO: check config, set a public uri
        
        //TODO: fire filesystem events for other systems to parse the file reference (getid3 could listen here)
        
        $resource->content->addFile($fileRef);

        //TODO: remove files if necessary, 

        //set the modified resource and stop propagation of this event
        $e->setResource($resource);
    }

}
