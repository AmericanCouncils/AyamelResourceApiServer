<?php

namespace Ayamel\ResourceBundle\Storage;

use Ayamel\ResourceBundle\Storage\ResourceStorageInterface;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Event\Events;
use Ayamel\ResourceBundle\Event\ResourceEvent;
use Ayamel\ResourceBundle\Event\GetResourceEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
* ResourceManager wraps another storage interface, but also fires pre and post events for any storge action taken.
 *
 * @author Evan Villemez
 */
class ResourceManager implements ResourceStorageInterface
{
    /**
     * The actual storage instance used.
     *
     * @var object Ayamel\ResourceBundle\Storage\ResourceStorageInterface
     */
    protected $storage;

    /**
     * A dispatcher for emitting storage events.
     *
     * @var object Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Constructor requires a ResourceStorageInterface, and dispatcher for emitting events.
     *
     * @param StorageInterface         $storage
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ResourceStorageInterface $storage, EventDispatcherInterface $dispatcher)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function persistResource(Resource $resource)
    {
        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);
        $resource = $this->storage->persistResource($event->getResource());

        return $this->dispatcher->dispatch(Events::POST_PERSIST, new ResourceEvent($resource))->getResource();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteResource(Resource $resource)
    {
        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch(Events::PRE_DELETE, $event);
        $resource = $this->storage->deleteResource($event->getResource());

        return $this->dispatcher->dispatch(Events::POST_DELETE, new ResourceEvent($resource))->getResource();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceById($id)
    {
        $event = new GetResourceEvent($id);
        $this->dispatcher->dispatch(Events::PRE_RETRIEVE, $event);

        if (!$resource = $event->getResource()) {
            $resource = $this->storage->getResourceById($id);
        }

        return $this->dispatcher->dispatch(Events::POST_RETRIEVE, new ResourceEvent($resource))->getResource();
    }
}
