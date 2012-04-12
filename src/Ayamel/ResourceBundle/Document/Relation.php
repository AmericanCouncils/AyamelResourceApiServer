<?php
namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;

/**
 * Relation object
 *
 * @MongoDB\EmbeddedDocument
 * 
 */
class Relation {
	
    /**
     * @MongoDB\String
	 * @JMS\SerializedName("subjectId")
     */
	protected $subjectId;
	
    /**
     * @MongoDB\String
	 * @JMS\SerializedName("objectId")
     */
	protected $objectId;
	
    /**
     * @MongoDB\String
     */
	protected $type;
	
    /**
     * @MongoDB\Hash
     */
	protected $attributes = array();

    /**
     * Get subjectId
     *
     * @return id $subjectId
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Set subjectId
     *
     * @return void
     */
    public function setSubjectId($id)
    {
        $this->subjectId = $id;
    }

    /**
     * Get objectId
     *
     * @return id $objectId
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set objectId
     *
     * @return void
     */
    public function setObjectId($id)
    {
        $this->objectId = $id;
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
     * Set all attributes
     *
     * @param hash $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get all attributes
     *
     * @return hash $attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
	
	/**
	 * Set an individual attribute by key for the attributes propery.
	 *
	 * @param string $key 
	 * @param mixed $val 
	 * @return self
	 */
	public function setAttribute($key, $val) {
		$this->attributes[$key] = $val;
		return $this;
	}
	
	/**
	 * Get an individual attribute by key, returns default value if not found
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getAttribute($key, $default = null) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
	}
	
	/**
	 * Remove an attribute by key if it exists.
	 *
	 * @param string $key 
	 * @return self
	 */
	public function removeAttribute($key) {
		if(isset($this->attributes[$key])) {
			unset($this->attributes[$key]);
		}
		
		return $this;
	}
	
	/**
	 * Return boolean if attribute exists
	 *
	 * @param string $key 
	 * @return boolean
	 */
	public function hasAttribute($key) {
		return isset($this->attributes[$key]);
	}
	
	/**
	 * Return true if a given relation instance is the same as this relation instance
	 *
	 * @param Relation $relation 
	 * @return void
	 * @author Evan Villemez
	 */
	public function equals(Relation $relation) {
		return (
			($this->subjectId === $relation->getSubjectId()) &&
			($this->objectId === $relation->getObjectId()) &&
			($this->type === $relation->getType()) &&
			($this->attributes == $relation->getAttributes())
		);
	}
}
