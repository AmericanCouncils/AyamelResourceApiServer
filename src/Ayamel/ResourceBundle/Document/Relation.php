<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

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
    use AutoGetterSetterTrait;

    /**
     * The unique ID of the resource.
     *
     * @MongoDB\Id
     * @JMS\Type("string")
     * @JMS\Groups({"search-decode"})
     */
    private $id;

    /**
     * The ID of the subject Resource.
     *
     * @MongoDB\String
     * @JMS\SerializedName("subjectId")
     * @JMS\Type("string")
     */
    protected $subjectId;

    /**
     * The ID of the object Resource.
     *
     * @MongoDB\String
     * @JMS\SerializedName("objectId")
     * @JMS\Type("string")
     */
    protected $objectId;

    /**
     * The type of the Relation.  Valid types include:
     *
     * - **based_on** - The subject is a performance, production, derivation, adaptation, or interpretation of the object resource.
     * - **references** - The subject cites or otherwise refers to the object resource.
     * - **requires** - The subject requires the object for its functioning, delivery, or content and cannot be used without the related resource being present.
     * - **transcript_of** - The subject is a linear description of a time-based object (e.g., a text transcript of audio)
     * - **search** - Content for the object resource will affect hits against the subject resource when searching.  Only owners of the subject Resource may create `search` relations.
     * - **version_of** - The subject is a historical state or edition of the object resource.
     * - **part_of** - The subject is a physical or logical part of the object resource.
     * - **translation_of** - The subject content is a translation into another human language of the object resource's content.
     * - **contains** - The subject resource contains the object resource.  This only applies to Resource of type "collection".
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * A key/val hash of properties relevant to the given "type".  For more details on which attributes
     * are valid for a given Relation type, check the documentation in the [project wiki](https://github.com/AmericanCouncils/AyamelResourceApiServer/wiki/Validation:-Relation-Attributes).
     *
     * @MongoDB\Hash
     * @JMS\Type("array")
     */
    protected $attributes = [];

    /**
     * An object containing information about the API client that created the object.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Client")
     * @JMS\ReadOnly
     * @JMS\Type("Ayamel\ResourceBundle\Document\Client")
     */
    protected $client;

    /**
     * An object containing information about the API client that created the object.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\ClientUser")
     * @JMS\SerializedName("clientUser")
     * @JMS\Type("Ayamel\ResourceBundle\Document\ClientUser")
     */
    protected $clientUser;

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
     * Set the client
     *
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Set the optional client user
     *
     * @param ClientUser $user
     */
    public function setClientUser(ClientUser $user = null)
    {
        $this->clientUser = $user;
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
