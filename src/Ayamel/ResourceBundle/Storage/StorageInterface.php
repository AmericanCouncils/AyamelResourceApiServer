<?php

namespace Ayamel\ResourceBundle\Storage;

use Ayamel\ResourceBundle\Document\Resource;

/**
 * A ManagerInterface instance must provide a way to persist Resource objects, performing basic CRUD functionality, and some bulk retrieval functionality.
 *
 * @author Evan Villemez
 */
interface StorageInterface {
    
    /**
     * Persist a resource to some storage system.
     *
     * @param Resource $resource - the resource before it has saved.
     * @return Resource - the resource after the save operation has completed
     */
    function persistResource(Resource $resource);
    
    /**
     * Remove a resource from the storage system
     *
     * @param Resource $resource 
     * @return Resource
     */
    function deleteResource(Resource $resource);
        
    /**
     * Retrieve a resource by an id
     *
     * @param string $id 
     * @return Resource
     */
    function getResourceById($id);
    
}
