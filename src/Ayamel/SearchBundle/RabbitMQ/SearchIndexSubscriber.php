<?php

namespace Ayamel\SearchBundle\RabbitMQ;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ayamel\ApiBundle\Event\Events as ApiEvents;
use AC\WebServicesBundle\EventListener\RestServiceSubscriber;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Ayamel\ApiBundle\Event\ResourceEvent;
use Ayamel\ApiBundle\Event\RelationEvent;

/**
 * Subscribes to API events and tells and notifies the search indexer via RabbitMQ
 * to reindex Resources when certain conditions are met.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class SearchIndexSubscriber implements EventSubscriberInterface
{
    protected $messages = array();
    private $registered = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            ApiEvents::RESOURCE_CREATED => 'onResourceModified',
            ApiEvents::RESOURCE_MODIFIED => 'onResourceModified',
            ApiEvents::RESOURCE_DELETED => 'onResourceDeleted',
            ApiEvents::RELATION_CREATED => 'onRelationEvent',
            ApiEvents::RELATION_DELETED => 'onRelationEvent'
        );
    }

    /**
     * Any Resource modifications should trigger a reindex.
     */
    public function onResourceModified(ResourceEvent $e)
    {
        if ('awaiting_content' === $e->getResource()->getStatus()) {
            return;
        }

        $this->messages[] = array(
            'id' => $e->getResource()->getId()
        );

        $this->registerTerminateListener();
    }

    public function onResourceDeleted(ResourceEvent $e)
    {
        $ids = array();

        $resource = $e->getResource();
        $ids[] = $resource->getId();

        //check resource relations for others to reindex (other resources may have linked to this one for search)
        if ($resource->getRelations()) {
            foreach ($resource->getRelations() as $relation) {
                if ('search' === $relation->getType() && $resource->getId() === $relation->getObjectId()) {
                    $ids[] = $relation->getSubjectId();
                }
            }
        }

        if (!empty($ids)) {
            $this->messages[] = array(
                'ids' => $ids
            );

            $this->registerTerminateListener();
        }
    }

    /**
     * Relations created by the Resource owner should trigger a reindex of the subject Resource.
     */
    public function onRelationEvent(RelationEvent $e)
    {
        $relation = $e->getRelation();
        $subject = $e->getSubjectResource();

        if ($relation->getClient()->getId() !== $subject->getClient()->getId()) {
            return;
        }

        $this->messages[] = array(
            'id' => $subject->getId()
        );

        $this->registerTerminateListener();
    }

    /**
     * Publish any RabbitMQ messages after the Response has gone out.
     */
    public function onApiTerminate(PostResponseEvent $e)
    {
        if (empty($this->messages)) {
            return;
        }


        $producer = $this->container->get('old_sound_rabbit_mq.search_index_producer');
        foreach ($this->messages as $message) {
            $producer->publish(serialize($message));
        }

        $this->messages = array();
    }

    private function registerTerminateListener()
    {
        if (!$this->registered) {
            $this->registered = true;
            $this->container->get('event_dispatcher')->addListener(RestServiceSubscriber::API_TERMINATE, array($this, 'onApiTerminate'));
        }
    }
}
