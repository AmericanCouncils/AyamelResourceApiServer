<?php

namespace Ayamel\ResourceBundle\Repository;

use Ayamel\ResourceBundle\Document\Relation;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Custom query methods for manipulating Relation documents.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class RelationRepository extends DocumentRepository
{

    /**
     * Get all relation documents where the given Resource is either subject
     * or the object.
     *
     * @param  string     $resourceId
     * @param  array|null $filters
     * @return array
     */
    public function getRelationsForResource($resourceId, $filters = array())
    {
        return $this->getQBForResourceRelations($resourceId, $filters)->getQuery()->execute();
    }

    /**
     * Remove relations for a resource, optionally restricting to other fields.
     *
     * @param string     $resourceId
     * @param array|null $filters
     */
    public function deleteRelationsForResource($resourceId, $filters = array())
    {
        return $this->getQBForResourceRelations($resourceId, $filters)->remove()->getQuery()->execute();
    }

    public function getQBForRelations($filters = array())
    {
        $qb = $this->createQueryBuilder('Relation');

        //and optionally other fields
        foreach ($filters as $field => $val) {
            if (is_array($val)) {
                $qb->field($field)->in($val);
            } else {
                $qb->field($field)->equals($val);
            }
        }

        return $qb;
    }

    public function getQBForResourceRelations($resourceId, $filters = array())
    {
        $qb = $this->createQueryBuilder('Relation');

        //Relations are always bi-directional, so get where the $resourceId is EITHER
        //the subject or the object
        $qb->addOr($qb->expr()->field('subjectId')->equals($resourceId));
        $qb->addOr($qb->expr()->field('objectId')->equals($resourceId));

        //and optionally other fields
        foreach ($filters as $field => $val) {
            if (is_array($val)) {
                $qb->field($field)->in($val);
            } else {
                $qb->field($field)->equals($val);
            }
        }

        return $qb;
    }
}
