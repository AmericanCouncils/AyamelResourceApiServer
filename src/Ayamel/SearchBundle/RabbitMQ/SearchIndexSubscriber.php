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
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            ApiEvents::RESOURCE_CREATED => 'onResourceEvent',
            ApiEvents::RESOURCE_MODIFIED => 'onResourceEvent',
            ApiEvents::RESOURCE_DELETED => 'onResourceEvent',
            ApiEvents::RELATION_CREATED => 'onRelationEvent',
            ApiEvents::RELATION_DELETED => 'onRelationEvent'
        );
    }
    
    /**
     * Any Resource modifications should trigger a reindex.
     */
    public function onResourceEvent(ResourceEvent $e)
    {
        // TODO: append new message
        
        $this->container->get('event_dispatcher')->addListener(RestServiceSubscriber::API_TERMINATE, array($this, 'onApiTerminate'));
    }
    
    /**
     * Search Relations should trigger a reindex.
     */
    public function onRelationEvent(RelationEvent $e)
    {
        $relation = $e->getRelation();
        if ('search' !== $relation->getType()) {
            return;
        }
        
        // TODO: append new message
        
        $this->container->get('event_dispatcher')->addListener(RestServiceSubscriber::API_TERMINATE, array($this, 'onApiTerminate'));
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
}
