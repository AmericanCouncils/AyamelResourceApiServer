<?php

namespace Ayamel\TranscodingBundle\RabbitMQ;

use Ayamel\ApiBundle\Event\Events as ApiEvents;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Ayamel\ResourceBundle\Document\FileReference;
use AC\WebServicesBundle\EventListener\RestServiceSubscriber;
use AC\Component\Transcoding\Transcoder;
use AC\Component\Transcoding\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Listens for upload events, and if it's of the proper type, registers a transcode job.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class PublisherListener
{
    private $container;
    private $uploaded_file;
    private $resource;
    
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
        $this->resource = $e->getResource();
        
        $this->uploadedReference = false;
        foreach ($this->resource->content->getFiles() as $file) {
            if ('original' === $file->getRepresentation() && $file->getInternalUri()) {
                $this->uploadedReference = $file;
                break;
            }
        }
            
        if (!$this->uploadedReference) {
            return;
        }
        
        $this->container->get('event_dispatcher')->addListener(RestServiceSubscriber::API_TERMINATE, array($this, 'onApiTerminate'));
    }
    
    /**
     * During API Terminate register job to transcode the modified resource
     */
    public function onApiTerminate(PostResponseEvent $e)
    {
        
        $this->container->get('old_sound_rabbit_mq.transcoding_producer')->publish(serialize(array(
            'id' => $this->resource->getId(),
            'notifyClient' => true,
        )));
    }
}
