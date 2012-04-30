<?php

namespace Ayamel\ResourceBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Special EventDispatcher used by the ResourceManager for storage events.  Will automatically check
 * for ResourceEvents and persist the Container accordingly.
 *
 * @author Evan Villemez
 */
class Dispatcher extends EventDispatcher {

    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
	protected $container;
	
    /**
     * This dispatcher will inject a ContainerInterface instance into events.
     *
     * @param ContainerInterface $container 
     * @author Evan Villemez
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function doDispatch($name, Event $event) {
        if($event instanceof ResourceEvent || $event instanceof GetResourceEvent) {
            $event->setContainer($this->container);
        }
        
        return parent::doDispatch($name, $event);
    }
}
