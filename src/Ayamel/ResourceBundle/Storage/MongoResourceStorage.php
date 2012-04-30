<?php

namespace Ayamel\ResourceBundle\Storage;

use Ayamel\ResourceBundle\Document\Resource;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * A ManagerInterface instance must provide a way to persist Resource objects, performing basic CRUD functionality, and some bulk retrieval functionality.
 *
 * @author Evan Villemez
 */
class MongoResourceStorage implements ManagerInterface {
    
    protected $repo;
    
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
