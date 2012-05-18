<?php

namespace Ayamel\ResourceBundle;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * This static class contains convenience methods for creating and modifying Resource objects from PHP arrays.
 *
 * @author Evan Villemez
 */
class ResourceDocumentsFactory {

    /**
     * Create a resource object from a nested PHP array.
     *
     * @param array $data 
     * @return Ayamel\ResourceBundle\Document\Resource;
     */
    static public function createResourceFromArray(array $data, Resource $preExistingResource = null) {
        $resource = ($preExistingResource) ? $preExistingResource : new Resource;
        
        //check for content field
        if(isset($data['content'])) {
            $resource->setContent(self::createContentCollectionFromArray($data['content']));
            unset($data['content']);
        }
        
        //check for relations
        if(isset($data['relations'])) {
            $relations = array();
            foreach($data['relations'] as $relation) {
                $relations[] = self::createRelationFromArray($relation);
            }
            $resource->setRelations($relations);
            unset($data['relations']);
        }
        
        //call setters on remaining top-level fields
        self::callSetters($resource, $data);

        return $resource;
    }
    
    /**
     * Modifies an existing resource object with a given data structure
     *
     * @param Resource $resource 
     * @param array $data 
     * @return Resource
     */
    static public function modifyResourceWithArray(Resource $resource, array $data) {
        return self::createResourceFromArray($data, $resource);
    }
    
    /**
     * Create Relation document from array of data
     *
     * @param array $data 
     * @return Ayamel\ResourceBundle\Document\Relation
     */
    static public function createRelationFromArray(array $data) {
        $relation = new Relation;
        self::callSetters($relation, $data);
        return $relation;
    }
    
    /**
     * Create FileReference document from array of data
     *
     * @param array $data 
     * @return Ayamel\ResourceBundle\Document\FileReference
     */
    static public function createFileReferenceFromArray(array $data) {
        $file = new FileReference;
        self::callSetters($file, $data);
        return $file;
    }
    
    /**
     * Create ContentCollection document from array of data.
     *
     * @param array $data 
     * @return Ayamel\ResourceBundle\Document\ContentCollection
     */
    static public function createContentCollectionFromArray(array $data) {
        $content = new ContentCollection;
        
        //check for files
        if(isset($data['files'])) {
            $files = array();
            foreach($files as $file) {
                $files[] = self::createFileReferenceFromArray($file);
            }

            $content->setFiles($files);
            unset($data['files']);
        }
        
        //call setters on remaining data
        self::callSetters($content, $data);
        return $content;
    }
    
    /**
     * Call setters on an object given a hash of fields/values.  Setter methods are derived from field names.
     *
     * @param object $object 
     * @param array $data 
     * @return void
     */
    static public function callSetters($object, array $data) {
        //assign received data
        foreach($data as $key => $val) {
            
            //derive setter method name            
            $method = 'set'.ucfirst($key);
            
            if(method_exists($object, $method)) {
                $object->$method($val);
            } else {
                throw new \InvalidArgumentException("Tried setting a non-existing field ($key)");
            }
        }

    }

}