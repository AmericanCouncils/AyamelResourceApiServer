<?php

namespace Ayamel\FilesystemBundle\EventListener;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
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
    
    /**
     * Constructor requires the Container for retrieving the filesystem service as needed.
     *
     * @param ContainerInterface $container 
     */
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
        
        //check if valid
        if (!$uploadedFile->isValid()) {
            throw new \RuntimeException(sprintf("File upload error %s", $uploadedFile->getError()));
        }

        //get filesystem
        $fs = $this->container->get('ayamel.api.filesystem');
            
        //add new file
        $filename = $this->cleanUploadedFileName($uploadedFile->getClientOriginalName());
        $uploadedRef = FileReference::createFromLocalPath($uploadedFile->getPathname());
        $newRef = $fs->addFileForId($resource->getId(), $uploadedRef, $filename, true);
            
        //inject relevant client-uploaded data, but only if it has not already been set by the
        //filesystem that handled the upload, as the client data may not be accurate
        if(!$newRef->getMime()) {
            $newRef->setMime($uploadedFile->getClientMimeType());
        }
        if(!$newRef->getAttribute('bytes', false)) {
            $newRef->setAttribute('bytes', $uploadedFile->getClientSize());
        }
            
        //the newly uploaded file is now the original reference
        $newRef->setOriginal(true);
        $newRef->setRepresentation("original;0");

        //set new content
        $resource->content->addFile($newRef);

        //set the modified resource and stop propagation of this event
        $resource->setStatus(Resource::STATUS_AWAITING_PROCESSING);
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
