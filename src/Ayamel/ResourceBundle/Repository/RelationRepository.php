<?php

namespace Ayamel\ResourceBundle\Repository;

use Ayamel\ResourceBundle\Document\Relation;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Custom query methods for retrieving Relation documents.
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
     * @param string $resourceId 
     * @param array $filters 
     * @return array
     */
    public function getRelationsForResource($resourceId, $filters = array())
    {
        $qb = $this->createQueryBuilder();
        
        //Relations are always bi-directional, so get where the $resourceId is EITHER
        //the subject or the object
        $qb->addOr($qb->expr()->field('subjectId', $resourceId));
        $qb->addOr($qb->expr()->field('objectId', $resourceId));
        
        //and optionally other fields
        foreach ($filters as $field => $val) {
            if (is_array($value)) {
                $qb->field($field)->in($val);
            } else {
                $qb->field($field)->equals($val);
            }
        }
        
        return $qb->getQuery()->execute();
    }
    
    public function deleteRelationsForResource($resourceId)
    {
        //TODO
    }

}
