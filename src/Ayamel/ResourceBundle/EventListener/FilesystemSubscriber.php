<?php

namespace Ayamel\ResourceBundle\EventListener;

use Ayamel\ResourceBundle\Event\Events;
use Ayamel\ResourceBundle\Event\ResourceEvent;
use Ayamel\ResourceBundle\Event\GetResourceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilesystemSubscriber implements EventSubscriberInterface {
	
    protected $fs;
    
    public function __construct(Filesystem $fs) {
        $this->fs = $fs;
    }
    
    public function getSubscribedEvents() {
        return array(
            Events::POST_PERSIST => 'onResourcePersisted',
            Events::POST_DELETE => 'onResourceDeleted',
        );
    }
    
    public function onResourceDeleted(ResourceEvent $e) {
        $resource = $e->getResource();
        
        //TODO: check fs for files owned by this object
    }
    
    public function onResourcePersisted(ResourceEvent $e) {
        $resource = $e->getResource();

        //TODO: check for original file, anything to do with it?
    }
}