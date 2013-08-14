<?php
namespace Ayamel\TranscodingBundle\RabbitMQ;

use Ayamel\ApiBundle\Event\Events as AyamelEvents;
use Ayamel\ApiBundle\Event\ApiEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use AC\WebServicesBundle\EventListener\RestServiceSubscriber;
use AC\Transcoding\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Listens for upload events, and if it's of the proper type, registers a transcode job.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class PublisherListener implements EventSubscriberInterface
{
    private $container;
    private $uploadedData;
    private $resource;
    private $fileToTranscode;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AyamelEvents::HANDLE_UPLOADED_CONTENT => array('onHandleUploadedContent', 255),
            AyamelEvents::RESOURCE_MODIFIED => 'onResourceModified',
            RestServiceSubscriber::API_TERMINATE => 'onApiTerminate'
        );
    }

    /**
     * This listens early for upload events, and in the case of uploaded files
     * that should be transcoded it registers the other listeners defined here.
     *
     * @param ResolveUploadedContentEvent $e
     */
    public function onResolveUploadedContent(ResolveUploadedContentEvent $e)
    {
        $req = $e->getRequest();
        if ($file = $req->files->get('file', false)) {
            if ('false' !== $req->request->get('transcode', 'true')) {
                $this->container->get('event_dispatcher')->addSubscriber($this);
            }
        }

    }

    /**
     * Only enables if a handling a file upload via the api
     */
    public function onHandleUploadedContent(HandleUploadedContentEvent $e)
    {
        //keep track of uploaded data, but don't perform any actions yet
        $this->uploadedData = $e->getContentData();
    }

    /**
     * If this is called, then the Resource was successfully modified with the new
     * file, so compare the resource with the original uploaded data, to be sure
     * of which file should be transcoded.
     */
    public function onResourceModified(ApiEvent $e)
    {
        $this->resource = $e->getResource();
        $uploadedFile = $this->uploadedData['file'];

        foreach ($this->resource->content->getFiles() as $file) {
            if ('original' === $file->getRepresentation() && $file->getInternalUri()) {
                $this->fileToTranscode = $file;

                return;
            }
        }
    }

    /**
     * After the request terminates, if we have a file that needs
     * to be transcoded, check the necessary config and schedule
     * any transcode jobs.
     *
     * This should take into account previously schedule jobs, so as not to schedule
     * files to be transcoded more than one time.
     */
    public function onApiTerminate(PostResponseEvent $e)
    {
        //TODO: change to dispatch multiple transcode jobs
        //and track state in cache
        //specify total number of jobs scheduled so consuming process knows when a full
        //transcode is finished and client systems should be notified

        if ($this->fileToTranscode) {
            $this->container->get('old_sound_rabbit_mq.transcoding_producer')->publish(serialize(array(
                'id' => $this->resource->getId(),
                'path' => $this->fileToTranscode->getInternalUri(),
                'notifyClient' => true,
            )));
        }
    }
}
