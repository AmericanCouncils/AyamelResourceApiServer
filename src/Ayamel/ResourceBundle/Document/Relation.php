<?php
namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Relation object
 *
 * @MongoDB\EmbeddedDocument
 * 
 */
class Relation {
	
    /**
     * @MongoDB\String
     */
	protected $subject_id;
	
    /**
     * @MongoDB\String
     */
	protected $object_id;
	
    /**
     * @MongoDB\String
     */
	protected $type;
	
    /**
     * @MongoDB\Hash
     */
	protected $attributes = array();

    /**
     * Get subject_id
     *
     * @return id $subjectId
     */
    public function getSubjectId()
    {
        return $this->subject_id;
    }

    /**
     * Get object_id
     *
     * @return id $objectId
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set attributes
     *
     * @param hash $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get attributes
     *
     * @return hash $attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
