<?php

namespace Ayamel\ResourceApiBundle\EventListener;

use Ayamel\ResourceApiBundle\Filesystem\FilesystemInterface;
use Ayamel\ResourceApiBundle\Event\Events;
use Ayamel\ResourceApiBundle\Event\ApiEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Registers API event listeners for managing the filesystem when certain actions occur.
 *
 * @author Evan Villemez
 */
class FilesystemSubscriber implements EventSubscriberInterface {
	
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
            Events::RESOURCE_CREATED => 'onResourcePersisted',
            Events::RESOURCE_MODIFIED => 'onResourcePersisted',
            Events::RESOURCE_DELETED => 'onResourceDeleted',
            Events::CONTENT_UPLOADED => 'onContentUploaded',
        );
    }
    
    public function onResourceDeleted(ApiEvent $e) {
        $resource = $e->getResource();
        
        //TODO: check fs for files owned by this object
    }
    
    
    public function onResourcePersisted(ApiEvent $e) {
        $resource = $e->getResource();

        //TODO: check for original file, anything to do with it?
    }
    
    public function onContentUploaded(ApiEvent $e) {
        $resource = $e->getResource();
        
        $files = $resource->content->getFiles();
        
        //TODO: remove files if necessary, 
        
        $fs = $this->container->get('ayamel.api.filesystem');
        $fs->addFileForId($resource->getId(), $files[0]);
    }

}
