<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\Client;
use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class RelationsController extends ApiController
{
    /**
     * This route allows you to get, in greater detail, relations of a given Resource.
     *
     * By default, only relations created by the resources owner, and current requesting client of the object
     * are returned, however here you may specify filters to see relations created by any system, or only retrieve
     * relations of a specific type.
     *
     * Note that this will return relations where the requested Resource is EITHER the
     * subject OR the object of the Relation, unless otherwise specified.
     *
     * @ApiDoc(
     *      resource=true,
     *      output="Ayamel\ResourceBundle\Document\Relation",
     *      description="Get/filter Relations for Resource",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *          {"name"="type", "description"="Limit returned Resources to a certain type."},
     *          {"name"="client", "description"="Limit returned Relations to those owned by a specific user an API client."},
     *          {"name"="clientUser", "description"="Limit returned Relations to those owned by a specific user an API client."},
     *          {"name"="subject", "default"="false", "description"="Limit returned Relations where this Resource is the subject"},
     *          {"name"="object", "default"="false", "description"="Limit returned Relations where this Resource is the object"},
     *          {"name"="limit", "default"=20, "description"="Limit the number of ids to return."},
     *          {"name"="skip", "default"=0, "description"="Number of results to skip. Use this for paginating results."}
     *      }
     * );
     *
     * @param string $id
     */
    public function filterResourceRelations($id)
    {
        $resource = $this->getRequestedResourceById($id);
        $req = $this->getRequest();

        if ($resource->isDeleted()) {
            return $this->returnDeletedResource($resource);
        }

        $this->requireClientVisibility($resource);

        //build filters
        $filters = array();
        if ($types = $req->query->get('type', false)) {
            $filters['type'] = explode(',', $types);
        }
        if ($clients = $req->query->get('client', false)) {
            $filters['client.id'] = explode(',', $clients);
        } else {
            $filters['client.id'] = array($this->getApiClient()->id, $resource->getClient()->getId());
        }
        if ($clientUsers = $req->query->get('clientUser', false)) {
            $filters['clientUser'] = explode(',', $clientUsers);
        }

        $subject = $req->query->get('subject', false);
        $object = $req->query->get('object', false);

        if ($subject || $object) {
            if ($subject) $filters['subjectId'] = $id;
            if ($object) $filters['objectId'] = $id;
            $qb = $this->getRepo('AyamelResourceBundle:Relation')->getQBForRelations($filters);
        } else {
            $qb = $this->getRepo('AyamelResourceBundle:Relation')->getQBForRelations($filters);
            $qb->addOr($qb->expr()->field('subjectId')->equals($id));
            $qb->addOr($qb->expr()->field('objectId')->equals($id));
        }

        //set limit/skip
        $qb->limit($req->query->get('limit', 20));
        $qb->skip($req->query->get('skip', 0));

        $relations = $qb->getQuery()->execute();

        return $this->createServiceResponse(array(
            'relations' => $this->relationsToArray($relations)
        ), 200);
    }

    /**
     * This route allows you to filter Relations.
     *
     * By default, only relations created by requesting client system are returned, however here you may specify filters
     * to see relations created by any system, or only retrieve relations of a specific type.
     *
     * @ApiDoc(
     *      resource=true,
     *      output="Ayamel\ResourceBundle\Document\Relation",
     *      description="Get/filter Relations",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *          {"name"="id", "description"="Comma delimited string of Resources ids.  If this is provided, it will match on Relations where the Resource IDs are EITHER the subject or object."},
     *          {"name"="subjectId", "description"="Comma delimited string of Resource subjectIds"},
     *          {"name"="objectId", "description"="Comma delimited string of Resource objectIds"},
     *          {"name"="type", "description"="Limit returned Resources to a certain type."},
     *          {"name"="client", "description"="Limit returned Relations to those owned by a specific user an API client."},
     *          {"name"="clientUser", "description"="Limit returned Relations to those owned by a specific user an API client."},
     *          {"name"="limit", "default"=20, "description"="Limit the number of ids to return."},
     *          {"name"="skip", "default"=0, "description"="Number of results to skip. Use this for paginating results."}
     *      }
     * );
     *
     * @param string $id
     */
    public function filterRelations(Request $req)
    {
        $filters = array();

        //set filters
        if ($subIds = $req->query->get('subjectId', false)) {
            $filters['subjectId'] = explode(',', $subIds);
        }
        if ($objIds = $req->query->get('objectId', false)) {
            $filters['objectId'] = explode(',', $objIds);
        }
        if ($types = $req->query->get('type', false)) {
            $filters['type'] = explode(',', $types);
        }
        if ($clients = $req->query->get('client', false)) {
            $filters['client.id'] = explode(',', $clients);
        } else {
            $filters['client.id'] = array($this->getApiClient()->id);
        }
        if ($clientUsers = $req->query->get('clientUser', false)) {
            $filters['clientUser'] = explode(',', $clientUsers);
        }
        //create the query builder w/ filters
        $qb = $this->getRepo('AyamelResourceBundle:Relation')->getQBForRelations($filters);

        //set limit/skip
        $qb->limit($req->query->get('limit', 20));
        $qb->skip($req->query->get('skip', 0));

        //EITHER subject or object
        if ($req->query->get('id', false)) {
            $ids = explode(',', $req->get('id'));
            $qb->addOr($qb->expr()->field('subjectId')->in($ids));
            $qb->addOr($qb->expr()->field('objectId')->in($ids));
        }

        //execute query
        $relations = $qb->getQuery()->execute();

        return $this->createServiceResponse(array(
            'relations' => $this->relationsToArray($relations)
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
     * To create a Relation, the requesting client must be able to view both of the Resources
     * in the Relation.
     *
     * @ApiDoc(
     *      resource=true,
     *      input="Ayamel\ResourceBundle\Document\Relation",
     *      output="Ayamel\ResourceBundle\Document\Relation",
     *      description="Add a Relation between two Resources"
     * )
     *
     * @param string $id
     */
    public function createRelation()
    {
        $this->requireAuthentication();

        $request = $this->getRequest();

        //create the relation submitted by the client
        $relation = $this->container->get('ac.webservices.object_validator')->createObjectFromRequest('Ayamel\ResourceBundle\Document\Relation', $this->getRequest());

        //retrieve the related resources
        $subject = $this->getRequestedResourceById($relation->getSubjectId());
        $object = $this->getRequestedResourceById($relation->getObjectId());

        //check for deleted objects
        if ($subject->isDeleted()) {
            throw $this->createHttpException(400, "Invalid subject id.");
        }
        if ($object->isDeleted()) {
            throw $this->createHttpException(400, "Invalid object id.");
        }

        //validate ownership depending on type
        $client = $this->getApiClient();
        switch ($relation->getType()) {
            case 'requires': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'transcript_of': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'references': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'based_on': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'translation_of': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'search': $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client); break;
            case 'version_of': $this->requireSubjectAndObjectOwnership($subject, $object, $client); break;
            case 'part_of': $this->requireSubjectAndObjectOwnership($subject, $object, $client); break;
            default : $this->requireSubjectOwnershipAndObjectVisibility($subject, $object, $client);
        }

        //fill in the other info
        $clientDoc = $client->createClientDocument();
        $relation->setClient($clientDoc);

        //validate and actually save the relation in storage
        $this->validateObject($relation);
        $manager = $this->get('doctrine_mongodb')->getManager();
        $manager->persist($relation);
        $manager->flush();

        //TODO: trigger 'modified' in resource - depending on what the relation is (search?)
        return $this->createServiceResponse(array('relation' => $relation), 201);
    }

    /**
     * Delete a Relation by it's unique id.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Delete a Resource Relation"
     * )
     *
     * @param string $resourceId
     * @param string $relationId
     */
    public function deleteRelation($id)
    {
        $this->requireAuthentication();
        //get the relation
        $repo = $this->getRepo('AyamelResourceBundle:Relation');
        $relation = $repo->find($id);

        //only owners may delete the relation
        if ($this->getApiClient()->id !== $relation->getClient()->getId()) {
            throw $this->createHttpException(403, "You are not the owner of this Relation.");
        }

        //remove the specific relation
        $manager = $this->getDocManager();
        $manager->remove($relation);
        $manager->flush();

        //TODO: resource modified if relation === search ?
        return $this->createServiceResponse(null, 200);
    }
    
    protected function requireSubjectOwnershipAndObjectVisibility($sub, $obj, $client)
    {
        if ($sub->getClient()->getId() !== $client->id) {
            throw $this->createHttpException(403, "You must be owner the subject Resource to create this type of Relation.");
        }
        
        if ($obj->getVisibility() && !in_array($client->id, $obj->getVisibility())) {
            throw $this->createHttpException(403, "You must be able to view the object Resource to create this type of Relation.");
        }
    }
    
    protected function requireSubjectAndObjectOwnership($sub, $obj, $client)
    {
        if ($sub->getClient()->getId() !== $client->id || $obj->getClient()->getId() !== $client->id) {
            throw $this->createHttpException(403, "You must own the subject and object Resources to create this type of Relation.");
        }
    }

    protected function relationsToArray($relations)
    {
        $rels = array();

        foreach (iterator_to_array($relations) as $key => $val) {
            $rels[] = $val;
        }

        return $rels;
    }

}
