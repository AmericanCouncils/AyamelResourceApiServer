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

//TODO: a little more care in when the original flag and mime is set - transcoding needs
//a reliable place to listen for when to schedule new transcoding jobs

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
     * PHP's default upload error constants with text explanations.
     *
     * @var array
     */
    private $uploadErrorTexts = array(
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize setting.",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the submited form.",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION => "A server extension stopped the file upload."
    );
    
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
            
            //TODO: optionally allow the client to ALSO specify the
            //representation, for example "transcoding;3"
            
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
            $msg = isset($this->uploadErrorTexts[$uploadedFile->getError()]) ? $this->uploadErrorTexts[$uploadedFile->getError()] : "Generic file upload error.";
            throw new \RuntimeException($msg);
        }

        //get filesystem
        $fs = $this->container->get('ayamel.api.filesystem');
            
        //clean the filename
        $receivedName = ($uploadedFile->getClientOriginalName()) ? $uploadedFile->getClientOriginalName() : $uploadedFile->getTempName();
        $filename = $this->cleanUploadedFileName($receivedName);

        //create a file reference for the uploaded file
        $uploadedRef = FileReference::createFromLocalPath($uploadedFile->getPathname());
        
        //save it to the filesystem (which may modify the reference to include additional information)
        $newRef = $fs->addFileForId($resource->getId(), $uploadedRef, $filename, true);
        
        //inject relevant client-uploaded data, but only if it has not already been set by the
        //filesystem that handled the upload, as the client data may not be accurate
        if(!$newRef->getMimeType()) {
            $mime = ($uploadedFile->getClientMimeType()) ? $uploadedFile->getClientMimeType() : $uploadedFile->getMimeType();
            $newRef->setMimeType($mime);
        }
        if(!$newRef->getAttribute('bytes', false)) {
            $newRef->setAttribute('bytes', $uploadedFile->getClientSize());
        }
            
        //the newly uploaded file is now the original reference
        //TODO: make this optional, there may already be an original
        //update this to allow the client to specify
        $newRef->setOriginal(true);
        $newRef->setRepresentation("original");
        $newRef->setQuality(1);

        //set new content
        $resource->content->addFile($newRef);

        //if this is the original reference, set the status properly
        if ("original" === $newRef->getRepresentation()) {
            $resource->setStatus(Resource::STATUS_AWAITING_PROCESSING);
        } else {
            $resource->setStatus(Resource::STATUS_NORMAL);
        }
        
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
        //if there's an extension, save it, and call it "original"
        $exp = explode(".", $name);
        if (count($exp > 1)) {
            $ext = end($exp);
            return 'original.' . $ext;
        }
        
        return "original";
    }

}
