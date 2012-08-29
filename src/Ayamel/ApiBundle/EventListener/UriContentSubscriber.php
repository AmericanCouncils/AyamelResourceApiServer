<?php

namespace Ayamel\ApiBundle\EventListener;

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
 * Registers API event listeners for handling special URI uploads.
 *
 * @author Evan Villemez
 */
class UriContentSubscriber implements EventSubscriberInterface {
	
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
            Events::RESOLVE_UPLOADED_CONTENT => 'onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => 'onHandleContent',
        );
    }
    
    /**
     * Check incoming request for a string uri, either as a post field, or json structure.
     *
     * @param ResolveUploadedContentEvent $e 
     */
    public function onResolveContent(ResolveUploadedContentEvent $e) {
        $request = $e->getRequest();
        $uri = false;
        if($json = json_decode($request->getContent())) {
            $uri = isset($json->uri) ? $json->uri : false;
        } else {
            $uri = $request->request->get('uri', false);
        }
        
        if(!$uri) return;
        
        $exp = explode("://", $uri);
        if(2 === count($exp)) {
            if($this->container->get('ayamel.resource.provider')->handlesScheme($exp[0])) {
                $e->setContentType('uri');
                $e->setContentData($uri);
            }
        }
    }
    
    /**
     * Process a specified uri and modify resource accordingly.
     *
     * @param HandleUploadedContentEvent $e 
     */
    public function onHandleContent(HandleUploadedContentEvent $e) {
        if('uri' !== $e->getContentType()) {
            return;
        }
        
        //try to derive a resource from the received uri
        $uri = $e->getContentData();
        if(!$derivedResource = $this->container->get('ayamel.resource.provider')->createResourceFromUri($uri)) {
            return;
        }
        
        $resource = $e->getResource();
        
        //set content properly
        $resource->content = $derivedResource->content;
        $originalRef = FileReference::createFromDownloadUri($uri);
        if(!$resource->content->hasFile($originalRef)) {
            $originalRef->setOriginal(true);
            $resource->content->addFile($originalRef);
        }
        
        //TODO: implement origin
        //persist origin field not specified by client, and available in derived resource
        /*
        if(!$resource->getOrigin() && $derivedResource->getOrigin()) {
            $resource->origin = $derivedResource->origin;
        }
        */
        
        //set the modified resource and stop propagation of this event
        $resource->setStatus(Resource::STATUS_OK);
        $e->setResource($resource);
    }

}
