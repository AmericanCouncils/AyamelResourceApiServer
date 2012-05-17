<?php

namespace Ayamel\FilesystemBundle\EventListener;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\Document\ContentCollection;
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
class FileUploadContentSubscriber implements EventSubscriberInterface {
	
    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Array of events subscribed to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        //TODO: consider setting the priorities for this to very low, in order to give other listeners a chance to handle specific file types earlier
        return array(
            Events::REMOVE_RESOURCE_CONTENT => 'onRemoveContent',
            Events::RESOLVE_UPLOADED_CONTENT => 'onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => 'onHandleContent',
        );
    }
    
    /**
     * Tell filesystem to remove files for a given resource.
     *
     * @param ApiEvent $e 
     */
    public function onRemoveContent(ApiEvent $e)
    {
        $this->container->get('ayamel.api.filesystem')->removeFilesForId($e->getResource()->getId());
    }
    
    /**
     * Check incoming request for an uploaded file.
     *
     * @param ResolveUploadedContentEvent $e 
     */
    public function onResolveContent(ResolveUploadedContentEvent $e)
    {
        $request = $e->getRequest();
                
        if ($file = $request->files->get('file', false)) {
            $e->setContentType('file_upload');
            $e->setContentData($file);
        }
    }
    
    /**
     * Handle a file upload and modify resource accordingly.
     *
     * @param HandleUploadedContentEvent $e 
     */
    public function onHandleContent(HandleUploadedContentEvent $e)
    {
        if ('file_upload' !== $e->getContentType()) {
            return;
        }
        
        //get the uploaded file, and the api filesystem
        $uploadedFile = $e->getContentData();
        $resource = $e->getResource();
        
        //process files
        if ($uploadedFile->isValid()) {
            //remove old files for resource
            $fs = $this->container->get('ayamel.api.filesystem');
            $fs->removeFilesForId($resource->getId());
            
            //add new file
            $filename = $this->cleanUploadedFileName($uploadedFile->getClientOriginalName());
            $uploadedRef = FileReference::createFromLocalPath($uploadedFile->getPathname());
            $newRef = $fs->addFileForId($resource->getId(), $uploadedRef, $filename, true);
            
            //inject relevant client-uploaded data, but only if it has not already been set by the
            //filesystem that handled the upload, as the client data may not be accurate
            if(!$newRef->getAttribute('mime-type', false)) {
                $newRef->setAttribute('mime-type', $uploadedFile->getClientMimeType());
            }
            if(!$newRef->getAttribute('size', false)) {
                $newRef->setAttribute('size', $uploadedFile->getClientSize());
            }
            
            //the newly uploaded file is now the original reference
            $newRef->setOriginal(true);

        } else {
            throw new \InvalidArgumentException(sprintf("File upload error %s", $uploadedFile->getError()));
        }

        //set new content
        $resource->content = new ContentCollection;
        $resource->content->addFile($newRef);

        //set the modified resource and stop propagation of this event
        $e->setResource($resource);
    }
    
    /**
     * Remove disgusting crap from the client file name, there's sure to be tons.
     *
     * @param string $name 
     * @return string
     */
    protected function cleanUploadedFilename($name)
    {
        return preg_replace("/[^\w\.-]/", "-", strtolower($name));
    }

}
