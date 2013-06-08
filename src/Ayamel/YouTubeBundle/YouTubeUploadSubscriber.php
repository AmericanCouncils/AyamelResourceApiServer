<?php

namespace Ayamel\YouTubeBundle;

use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class YouTubeUploadSubscriber implements SubscriberInterface
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    //this registers the handlers to listen earlier than the default uri handler
    public static function getSubscribedEvents()
    {
        return array(
            Events::RESOLVE_UPLOADED_CONTENT => array('resolveContent', 128),
            Events::HANDLE_UPLOADED_CONTENT => array('handleContent', 128)
        );
    }

    public function onResolveContent(ResolveUploadedContentEvent $e)
    {
        $request = $e->getRequest();
        $uri = false;
        if ($json = json_decode($request->getContent())) {
            $uri = isset($json->uri) ? $json->uri : false;
        } else {
            $uri = $request->request->get('uri', false);
        }

        if(!$uri) {
            return;
        }
        
        $exp = explode('://', $uri);
        if ($uri[1] !== 'youtube') {
            return;
        }
        
        $e->setContentType('youtube');
        $e->setContentData($uri);
    }
    
    public function onHandleContent(HandleUploadedContentEvent $e)
    {
        if ('youtube' !== $e->getContentType()) {
            return;
        }
        
        //try to derive a resource from the received uri
        $uri = $e->getContentData();
        if (!$derivedResource = $this->container->get('ayamel.resource.provider')->createResourceFromUri($uri)) {
            throw new \RuntimeException(sprintf("Could not create a valid Resource from [%s]", $uri));
        }

        //TODO: properly merge new data into old data to provent losing information
        $oldResource = $e->getResource();
        $oldResource->content = $derivedResource->content;
        
        $derivedResource->setStatus(Resource::STATUS_NORMAL);
        $e->setResource($derivedResource);
    }
}
