<?php
namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;

/**
 * API Client object, which can contain optional user-specific data
 *
 * @MongoDB\EmbeddedDocument
 * 
 */
class Client {
    
    /**
     * The ID of the API client which created the Resource.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("id")
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $id;
    
    /**
     * A human-readable name of the API client.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("name")
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $name;
    
    /**
     * A URI referencing the API client, if applicable.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("uri")
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */    
    protected $uri;
    
    /**
     * An optional reference to an internal user of API client 
     * system who uploaded the resource.  This would most likely
     * be a unique ID from the client system.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("user")
     * @JMS\Type("string")
     */
    protected $user;
    
    /**
     * An optional URI referencing the internal user of the API
     * client who created the Resource.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("userUri")
     * @JMS\Type("string")
     */
    protected $userUri;

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

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = (string) $user;
    }

    /**
     * Get user
     *
     * @return string $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set userUri
     *
     * @param string $userUri
     */
    public function setUserUri($userUri)
    {
        $this->userUri = $userUri;
    }

    /**
     * Get userUri
     *
     * @return string $userUri
     */
    public function getUserUri()
    {
        return $this->userUri;
    }
}
