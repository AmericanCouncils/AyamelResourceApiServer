<?php

namespace Ayamel\ResourceBundle;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\Client;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * This static class contains convenience methods for creating and modifying Resource objects from PHP arrays.
 *
 * @author Evan Villemez
 */
class ResourceDocumentsFactory
{
    /**
     * Create a resource object from a nested PHP array.
     *
     * @param  array                                    $data
     * @return Ayamel\ResourceBundle\Document\Resource;
     */
    public static function createResourceFromArray(array $data, Resource $preExistingResource = null)
    {
        $resource = ($preExistingResource) ? $preExistingResource : new Resource;

        //check for content field
        if (isset($data['content'])) {
            $resource->setContent(self::createContentCollectionFromArray($data['content']));
            unset($data['content']);
        }

        //check for relations
        if (isset($data['relations'])) {
            $relations = array();
            foreach ($data['relations'] as $relation) {
                $relations[] = self::createRelationFromArray($relation);
            }
            $resource->setRelations($relations);
            unset($data['relations']);
        }

        //check for client
        if (isset($data['client'])) {
            $resource->setClient(self::createClientFromArray($data['client']));
            unset($data['client']);
        }

        //check for origin
        if (isset($data['origin'])) {
            $resource->setOrigin(self::createOriginFromArray($data['origin']));
            unset($data['origin']);
        }

        //call setters on remaining top-level fields
        self::callSetters($resource, $data);

        return $resource;
    }

    /**
     * Modifies an existing resource object with a given data structure
     *
     * @param  Resource $resource
     * @param  array    $data
     * @return Resource
     */
    public static function modifyResourceWithArray(Resource $resource, array $data)
    {
        return self::createResourceFromArray($data, $resource);
    }

    /**
     * Create Origin document from php array.
     *
     * @param  array  $data
     * @return Origin
     */
    public static function createOriginFromArray(array $data)
    {
        $origin = new Origin;
        self::callSetters($origin, $data);

        return $origin;
    }

    /**
     * Create Client document from php array.
     *
     * @param  array  $data
     * @return Client
     */
    public static function createClientFromArray(array $data)
    {
        $client = new Client;
        self::callSetters($client, $data);

        return $client;
    }

    /**
     * Create Relation document from array of data
     *
     * @param  array                                   $data
     * @return Ayamel\ResourceBundle\Document\Relation
     */
    public static function createRelationFromArray(array $data)
    {
        $relation = new Relation;
        self::callSetters($relation, $data);

        return $relation;
    }

    /**
     * Create FileReference document from array of data
     *
     * @param  array                                        $data
     * @return Ayamel\ResourceBundle\Document\FileReference
     */
    public static function createFileReferenceFromArray(array $data)
    {
        $file = new FileReference;
        self::callSetters($file, $data);

        return $file;
    }

    /**
     * Create ContentCollection document from array of data.
     *
     * @param  array                                            $data
     * @return Ayamel\ResourceBundle\Document\ContentCollection
     */
    public static function createContentCollectionFromArray(array $data)
    {
        $content = new ContentCollection;

        //check for files
        if (isset($data['files'])) {
            $files = array();
            foreach ($files as $file) {
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
     * @param  object $object
     * @param  array  $data
     * @return void
     */
    public static function callSetters($object, array $data)
    {
        //assign received data
        foreach ($data as $key => $val) {

            //HACK: ignore fields prepended with underscores
            if (0 !== strpos($key, "_")) {
                //derive setter method name
                $method = 'set'.ucfirst($key);

                //call if it exists, if not, invalid argument
                if (method_exists($object, $method)) {
                    $object->$method($val);
                } else {
                    throw new \InvalidArgumentException("Tried setting a non-existing field [$key]");
                }
            }
        }
    }

}
