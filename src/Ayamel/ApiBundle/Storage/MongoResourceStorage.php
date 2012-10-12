<?php

namespace Ayamel\ApiBundle\Storage;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Storage\StorageInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Implements basic Resource object storage in MongoDB using Doctrine.
 *
 * @author Evan Villemez
 */
class MongoResourceStorage implements StorageInterface {
    
    /**
     * @var object Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $manager;
    
    /**
     * Constructor requires a Doctrine Mongo DocumentRepository instance.
     *
     * @param DocumentRepository $manager 
     */
    public function __construct(DocumentManager $manager) {
        $this->manager = $manager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function persistResource(Resource $resource) {
        $date = new \DateTime();
        if(!$resource->getId()) {
    		$resource->setDateAdded($date);
        }
    	$resource->setDateModified($date);
                
        $this->manager->persist($resource);
        $this->manager->flush();

        return $resource;
    }
    
    /**
     * {@inheritdoc}
     *
     * Note:  The API's Mongo implementation will never actually delete a resource from storage, rather it
     * will mark a resource as having been deleted, noting the date, thus preserving it's unique id and allowing
     * handling of future errors properly.
     * 
     */
    public function deleteResource(Resource $resource) {

        //unset all fields (for now)
        //TODO: preserve certain fields
        foreach(get_class_methods($resource) as $method) {
            if(0 === strpos($method, 'set')) {
                $resource->$method(null);
            }
        }
        
        //set delete date and status
        $resource->setDateDeleted(new \DateTime());
        $resource->setStatus(Resource::STATUS_DELETED);
        
        $this->manager->persist($resource);
        $this->manager->flush();
        
        return $resource;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getResourceById($id) {
        return $this->manager->getRepository('AyamelResourceBundle:Resource')->find($id);
    }
    
    public function getResourcesByIds(array $ids)
    {
        throw new \RuntimeException(sprintf("Method [%s] not yet implemented.", __METHOD__));
    }
}
