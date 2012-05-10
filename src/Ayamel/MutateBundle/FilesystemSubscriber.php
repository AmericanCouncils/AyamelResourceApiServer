<?php

namespace Ayamel\MutateBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ayamel\FilesystemBundle\Event\FileReferenceAddedEvent;

/**
 * Listens for Filesystem events and adds new files to transcode queue if applicable.
 *
 * @author Evan Villemez
 */
class FilesystemSubscriber implements EventSubscriberInterface {

	protected $container;
	
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public static function getSubscribedEvents() {
		return array(
			Events::FILESYSTEM_POST_ADD => 'onReferenceAdded'
		);
	}
	
	public function onReferenceAdded(FileReferenceAddedEvent $e) {
		$ref = $e->getFileReference();
		
		//only transcode original files
		if(!$ref->getOriginal()) {
			return;
		}
		$id = $e->getResourceId();
		
		//get the resource
		$resource = $this->container->get('ayamel.resource.manager')->find($id);
		
		//get the uploading client
		
		//check client configs for transcode settings
		
		//TODO: register a kernel.terminate listener to add the new file into the transcode queue
	}

}