<?php

namespace Ayamel\FilesystemBundle\EventListener;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\ResourceDocumentsFactory;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles recording content as a remote files array.  The validity of the actual file locations are not checked (currently).
 *
 * @author Evan Villemez
 */
class RemoteFilesContentSubscriber implements EventSubscriberInterface
{
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
        return array(
            Events::RESOLVE_UPLOADED_CONTENT => 'onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => 'onHandleContent',
        );
    }

    /**
     * Check incoming request for JSON that specifies a files array with remote files.
     *
     * @param ResolveUploadedContentEvent $e
     */
    public function onResolveContent(ResolveUploadedContentEvent $e)
    {
        $request = $e->getRequest();

        //if no json, or `remoteFiles` key isn't set, skip
        $body = $e->getRequestBody();

        if (!$body || !isset($body['remoteFiles']) || !is_array($body['remoteFiles'])) {
            return;
        }

        //create FileReference instances
        $remoteFiles = array();
        try {
            foreach ($body['remoteFiles'] as $fileData) {

                //TODO: need some type of validation on this, as clients can put anything into the `attributes` field
                
                //TODO: remove references to ResourceDocumentsFactory, replace with object validator
                $remoteFiles[] = ResourceDocumentsFactory::createFileReferenceFromArray($fileData);
            }
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }

        //if we didn't actually create anything, just return to let others try and process the event
        if (empty($remoteFiles)) {
            return;
        }

        //TODO: consider validating the remote files to disallow non-existant files.  Could make a HEAD request to avoid pulling lots of data.

        //notify the event as having been handled
        $e->setContentType('remote_files');
        $e->setContentData($remoteFiles);
    }

    /**
     * Handle a file upload and modify resource accordingly.
     *
     * @param HandleUploadedContentEvent $e
     */
    public function onHandleContent(HandleUploadedContentEvent $e)
    {
        if ('remote_files' !== $e->getContentType()) {
            return;
        }

        $resource = $e->getResource();

        //set new content
        foreach ($e->getContentData() as $fileRef) {
            $resource->content->addFile($fileRef);
        }

        //set the modified resource and stop propagation of this event
        $resource->setStatus(Resource::STATUS_NORMAL);
        $e->setResource($resource);
    }

}
