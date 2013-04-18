<?php

namespace Ayamel\ResourceBundle\Repository;

use Ayamel\ResourceBundle\Document\Resource;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Custom query methods for managing Resource documents.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class ResourceRepository extends DocumentRepository
{
	
    /**
     * Deleting a resource does not actually remove it from storage, instead
     * it clears most fields, and stores the date on which it was deleted.
     *
     * @param Resource $resource 
     * @return Resource
     */
	public function deleteResource(Resource $resource) {
        //unset all fields (for now)

        //TODO: preserve some fields ?
        // - client object
        // - date added

        foreach (get_class_methods($resource) as $method) {
            if (0 === strpos($method, 'set')) {
                $resource->$method(null);
            }
        }

        //set delete date and status
        $resource->setDateDeleted(new \DateTime());
        $resource->setStatus(Resource::STATUS_DELETED);

        return $resource;		
	}

}
