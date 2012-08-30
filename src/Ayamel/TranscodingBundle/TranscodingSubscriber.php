<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ApiBundle\Event\Events as ApiEvents;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;

/**
 * Listens for upload events, and if it's of the proper type, registers a transcode job.
 *
 * @package TranscodingBundle
 * @author Evan Villemez
 */
class TranscodingListener
{
    private $container;
    private $uploaded_file;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Only enables if a handling a file upload via the api
     */
    public function onHandleUploadedContent(HandleUploadedContentEvent $e)
    {
        if ('file_upload' !== $e->getContentType()) {
            return;
        }
        
        //keep track of newly uploaded file reference
        $this->uploaded_file = $e->getContentData();
        
        //dynamically register a listener for the api resource modified event
        $this->container->get('ayamel.api.dispatcher')->addListener(ApiEvents::RESOURCE_MODIFIED, array($this, 'onResourceModified'));
    }
    
    /**
     * Scan the resource content for an original file with an internal uri
     * and schedule any transcode jobs for it.
     */
    public function onResourceModified(ApiEvent $e)
    {
        $resource = $e->getResource();
            
        $uploadedFile = false;
        foreach ($resource->content->getFiles() as $file) {
            if ($file->getInternalUri() && $file->equals($this->uploaded_file)) {
                $uploadedFile = $this->uploaded_file;
                break;
            }
        }
            
        if (!$uploadedFile) {
            return;
        }

        //TODO: how to tell if I can/should schedule a transcode job?
        throw new \RuntimeException("Cannot transcode files yet... some things need to be resolved.");
    }
}
