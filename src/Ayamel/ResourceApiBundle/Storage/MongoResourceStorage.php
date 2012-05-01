<?php

namespace Ayamel\ResourceApiBundle\Storage;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Storage\StorageInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Implements basic Resource object storage in MongoDB using Doctrine.
 *
 * @author Evan Villemez
 */
class MongoResourceStorage implements StorageInterface {
    
    /**
     * undocumented variable
     *
     * @var object Doctrine\ODM\MongoDB\DocumentRepository
     */
    protected $repo;
    
    /**
     * Constructor requires a Doctrine Mongo DocumentRepository instance.
     *
     * @param DocumentRepository $repo 
     */
    public function __construct(DocumentRepository $repo) {
        $this->repo = $repo;
    }
    
    /**
     * {@inheritdoc}
     */
    function persistResource(Resource $resource) {
        $date = new \DateTime();
        if($resource->getId()) {
    		$resource->setDateModified($date);
        } else {
    		$resource->setDateAdded($date);
    		$resource->setDateModified($date);
        }
                
        $this->repo->persist($resource);
        $this->repo->flush();

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
    function deleteResource(Resource $resource) {

        //unset all fields (for now)
        foreach(get_class_methods($resource) as $method) {
            if(0 === strpos($method, 'set')) {
                $resource->$method(null);
            }
        }
        
        //set delete date and status
        $resource->setDateDeleted(new \DateTime());
        $resource->setStatus(Resource::STATUS_DELETED);
        
        $this->repo->persist($resource);
        $this->repo->flush();
        
        return $resource;
    }
        
    /**
     * {@inheritdoc}
     */
    function getResourceById($id) {
        return $repo->getRepository('AyamelResourceBundle:Resource')->find($id);
    }
    
}
