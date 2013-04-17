<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

//TODO: dateAdded field?

/**
 * Relation object that describes a type of relationship between two resource objects.
 * 
 * @MongoDB\Document(
 *      collection="relations",
 *      repositoryClass="Ayamel\ResourceBundle\Repository\RelationRepository"
 * )
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class Relation
{
    /**
     * The unique ID of the resource.
     *
     * @MongoDB\Id
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $id;

    /**
     * The ID of the subject Resource.
     *
     * @MongoDB\String
     * @JMS\SerializedName("subjectId")
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $subjectId;

    /**
     * The ID of the object Resource.
     *
     * @MongoDB\String
     * @JMS\SerializedName("objectId")
     * @JMS\Type("string")
     * @Assert\NotBlank
     */
    protected $objectId;
    
    /**
     * The type of the Relation.  Valid types include:
     *
     * - **part_of** - words here...
     * - **requires** - words here...
     * - **depends_on** - words here...
     *
     * @MongoDB\String
     * @JMS\Type("string")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * A key/val hash of properties relevant to the given "type".
     *
     * @MongoDB\Hash
     * @JMS\Type("array")
     */
    protected $attributes = array();
    
    /**
     * An object containing information about the API client that created the Resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Client")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Client")
     */    
    protected $client;

    /**
     * Get unique id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
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
     * @param  string $key
     * @param  mixed  $val
     * @return self
     */
    public function setAttribute($key, $val)
    {
        $this->attributes[$key] = $val;

        return $this;
    }

    /**
     * Get an individual attribute by key, returns default value if not found
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    /**
     * Remove an attribute by key if it exists.
     *
     * @param  string $key
     * @return self
     */
    public function removeAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * Return boolean if attribute exists
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
    
    /**
     * Set the client
     *
     * @param Client $client 
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Return true if a given relation instance is the same as this relation instance
     *
     * @param  Relation $relation
     * @return boolean
     */
    public function equals(Relation $relation)
    {
        return (
            ($this->subjectId === $relation->getSubjectId()) &&
            ($this->objectId === $relation->getObjectId()) &&
            ($this->type === $relation->getType()) &&
            ($this->attributes == $relation->getAttributes())
        );
    }
}
