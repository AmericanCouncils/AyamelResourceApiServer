<?php
namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * API Client object, which can contain optional user-specific data.
 *
 * @MongoDB\EmbeddedDocument
 *
 * @package AyamelResourceBundle
 */
class Client
{
    /**
     * The ID of the API client which created the Resource.  Ids in this case
     * are unique string representations, not database-assigned values.
     *
     * @MongoDB\String
     * @JMS\SerializedName("id")
     * @JMS\Type("string")
     * @JMS\Groups({"search-decode"})
     */
    protected $id;

    /**
     * A human-readable name of the API client.
     *
     * @MongoDB\String
     * @JMS\SerializedName("name")
     * @JMS\Type("string")
     * @JMS\Groups({"search-decode"})
     */
    protected $name;

    /**
     * A URI referencing the API client, if applicable.
     *
     * @MongoDB\String
     * @JMS\SerializedName("uri")
     * @JMS\Type("string")
     * @JMS\Groups({"search-decode"})
     */
    protected $uri;

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set uri
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get uri
     *
     * @return string $uri
     */
    public function getUri()
    {
        return $this->uri;
    }
}
