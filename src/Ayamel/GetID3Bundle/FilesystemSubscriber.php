<?php

namespace Ayamel\GetID3Bundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Cache\Cache;
use Ayamel\FilesystemBundle\Event\Events;
use Ayamel\FilesystemBundle\Event\FilesystemEvent;

/**
 * Populates FileReference objects with meta-data derived from the `getid3` library
 */
class FilesystemSubscriber implements EventSubscriberInterface {
	
	protected $container;
	
	protected $cache = false;
    
    public function __construct(ContainerInterface $container, Cache $cache = null) {
        $this->container = $container;
        if($cache) {
            $this->cache = $cache;
        }
    }
	
	public static function getSubscribedEvents() {
		return array(
			Events::FILESYSTEM_POST_ADD => 'onReturnFileReference',
			Events::FILESYSTEM_RETRIEVE => 'onReturnFileReference'
		);
	}
	
    /**
     * If a reference has an internal uri, inject file metadata if possible.
     *
     * @param FilesystemEvent $e 
     * @return void
     */
	public function onReturnFileReference(FilesystemEvent $e) {
	    $ref = $e->getFileReference();
        if(!$ref->getInternalUri()) {
            return;
        }
        
        if($stats = $this->getStatsForFile($ref->getInternalUri())) {
            $ref->setAttributes($stats);
        }
	}
	
    /**
     * Get the getid3 stats for a file path, checking for cached results first
     *
     * @param string $path 
     * @return array or false
     */
    protected function getStatsForFile($path) {
        $cacheKey = $path."_getid3";
        
        //check cache first
        if($this->cache) {
            if($stats = $this->cache->get($cacheKey)) {
                return $stats;
            }
        }
        
        //otherwise actually use getid3, and cache the results
        $getid3 = new \getID3;
        if($data = $getid3->analyze($path)) {
            $stats = $this->organizeGetid3Results($data);
            $this->cache->save($cacheKey, $stats, 0);
            return $stats;
        }
        
        return false;
    }
    
    /**
     * Return nicely structured data for the FileReference to be stored in the attributes field.  May require
     * some mangling depending on what getid3 returns
     *
     * @param array $data - array of data as returned by getid3
     * @return array - nicely formated final data to be injected into FileReference object
     */
    protected function organizeGetid3Results(array $data) {
        
        //TODO: mangle data here, or fire an event to handle the mangling
        
        return $data;
    }
}
