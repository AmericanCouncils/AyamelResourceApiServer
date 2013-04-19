<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\Client;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Resource Relations CRUD controller.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class RelationsController extends ApiController
{
    /**
     * This route allows you to get, in greater detail, relations of a given Resource.
     *
     * By default, only relations created by the client owner of the object are returned, however
     * here you may specify filters to see relations created by any system, or only retrieve
     * relations of a specific type.
     *
     * Note that this will return all relations where the requested Resource is EITHER the
     * subject OR the object of the Relation.
     *
     * @ApiDoc(
     *      resource=true,
     *      output="Ayamel\ResourceBundle\Document\Relation",
     *      description="Get/filter Relations for Resource"
     * )
     *
     * @param  string $id
     */
    public function getResourceRelations($id)
    {
        $resource = $this->getRequestedResourceById($id);
        $request = $this->getRequest();
        
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        $filters = array();
        
        //TODO: only get the relations the requesting client is allowed to see
        
        //check for type filter
        if ($types = $request->query->get('type', false)) {
            $filters['type'] = explode(',', $types);
        }
        
        //get the relations into an array
        $repo = $this->getRepo('AyamelResourceBundle:Relation');
        $relations = $repo->getRelationsForResource($resource->getId(), $filters);
        $rels = array();
        if ($relations) {
            foreach (iterator_to_array($relations) as $rel) {
                $rels[] = $rel;
            }
        }
        
        return $this->createServiceResponse(array(
            'relations' => $rels
        ), 200);
    }

    /**
     * Create a Relation, linking one Resource to another.
     *
     * Relations are critical for the search indexing process.  For example, if
     * you have text content that relates to a video Resource, specifying the
     * Relation between the two properly will ensure that search hits against the
     * text content will affect the ranking of the related video in the result.
     *
     * By default, only client systems that own a Resource can create Relations for
     * it.
     *
     * When adding a relation, you only need to specify the Relation `objectId`.  The
     * `subjectID` is automatically determined based on the Resource to which the
     * Relation is being added.
     *
     * @ApiDoc(
     *      resource=true,
     *      input="Ayamel\ResourceBundle\Document\Relation",
     *      output="Ayamel\ResourceBundle\Document\Relation",
     *      description="Add a Relation between two Resources"
     * )
     *
     * @param  string $id
     */
    public function createResourceRelation($id)
    {
        $request = $this->getRequest();
        
        //get the resource
        $resource = $this->getRequestedResourceById($id);
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        //create the relation submitted by the client
        $relation = $this->container->get('ac.webservices.object_validator')->createObjectFromRequest('Ayamel\ResourceBundle\Document\Relation', $this->getRequest());
        
        //check if the object resource is actually valid
        try {
            $object = $this->getRequestedResourceById($relation->getObjectId());
        } catch (\Exception $e) {
            throw $this->createHttpException(400, "Invalid object id.");
        }        
        if ($object->isDeleted()) {
            throw $this->createHttpException(400, "Invalid object id.");
        }
        
        //fill in the other info
        $relation->setSubjectId($resource->getId());
        if (!$relation->getClient()) {
            $relation->setClient(new Client());
        }
        if (!$relation->getClient()->getId()) {
            $request::trustProxyData();
            $relation->getClient()->setId($request->getClientIp());
        }
        
        //actually save the relation in storage
        $manager = $this->get('doctrine_mongodb')->getManager();
        $manager->persist($relation);
        $manager->flush();

        //TODO: trigger 'modified' in resource - depending on what the relation is (search?)
        
        return $this->createServiceResponse(array('relation' => $relation), 201);
    }

    /**
     * Delete a Relation for a Resource by it's unique id.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Delete a Resource Relation"
     * )
     *
     * @param  string $resourceId
     * @param  string $relationId
     */
    public function deleteResourceRelation($resourceId, $relationId)
    {
        //get the resource
        $resource = $this->getRequestedResourceById($resourceId);
        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }
        
        //TODO: validate that api client can view the resource
        if (false) {
            throw $this->createHttpException(403, "Not authorized to delete this Relation.");
        }
        
        $repo = $this->getRepo('AyamelResourceBundle:Relation');
        $relation = $repo->find($relationId);
        
        //TODO: verify that the API client owns the Relation
                
        //subjectId and resourceId must match
        if ($relation->getSubjectId() !== $resource->getId()) {
            throw $this->createHttpRequest(400, "The specified Resource must be the subject of this Relation.");
        }
        
        //remove the specific relation
        $manager = $this->getDocManager();
        $manager->remove($relation);
        $manager->flush();
        
        return $this->createServiceResponse(null, 200);
    }

}
