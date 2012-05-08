<?php

namespace Ayamel\ResourceApiBundle\EventListener;

use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Ayamel\ResourceApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ResourceApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

//NOTE: If more generic content request checking needs to happen, then all subscribers should extend this base subscriber
//TODO: Also... more content related methods, like Events::CONTENT_REMOVE and Events::CONTENT_MAINTENANCE (??)

/**
 * A base event subscriber for handling content uploads.
 *
 * @author Evan Villemez
 */
abstract class ContentSubscriber implements EventSubscriberInterface {
	
    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    /**
     * Array of events subscribed to.
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        return array(
            Events::RESOLVE_UPLOADED_CONTENT => '_onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => '_onHandleContent',
        );
    }
    
    public function _onResolveContent(ResolveUploadedContentEvent $e) {
        //TODO: general logic here
        if($e->isResolved()) {
            throw new \LogicException("Content has already been resolved...");
        }
        
        return $this->onResolveContent($e);
    }
    
    public function _onHandleContent(HandleUploadedContentEvent $e) {
        //TODO: general logic here
        
        return $this->onHandleContent($e);
    }
    
    /**
     * Check incoming request for content.  Should be implemented by extending classes.
     *
     * @param ResolveUploadedContentEvent $e 
     */
    protected function onResolveContent(ResolveUploadedContentEvent $e) {
        throw new \RuntimeException(sprintf("%s not implemented.", __METHOD__));
    }
    
    /**
     * Handle content from incoming request.  Should be implemented by extending classes.
     *
     * @param HandleUploadedContentEvent $e 
     */
    protected function onHandleContent(HandleUploadedContentEvent $e) {
        throw new \RuntimeException(sprintf("%s not implemented.", __METHOD__));
    }

}
