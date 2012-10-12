<?php

namespace Ayamel\ApiBundle\Controller\V1;

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
     *      return="Ayamel\ResourceBundle\Document\Relation",
     *      description="Get/filter Relations for Resource"
     * )
     * 
	 * @param string $id 
	 * @return array
	 */
    public function getResourceRelations($id)
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));
        
        $resource = $this->getRequestedResourceById($id);
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
     *      return="Ayamel\ResourceBundle\Document\Relation",
     *      description="Add a Relation between two Resources"
     * )
     * 
	 * @param string $id 
	 * @return array
	 */
	public function createResourceRelation($id)
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));

        $resource = $this->getRequestResourceById($id);
    }
    
    /**
     * Delete a Relation for a Resource by it's unique id.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Delete a Resource Relation"
     * )
     * 
     * @param string $resourceId 
     * @param string $relationId 
     * @return void
     * @author Evan Villemez
     */
    public function deleteResourceRelation($resourceId, $relationId)
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));
    }

}
