<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This is a reference to a specific user in a client system (optional).
 * 
 * @MongoDB\EmbeddedDocument
 *
 * @package AyamelResourceBundle
 */
class ClientUser
{
    /**
     * An optional reference to an internal user of API client
     * system who uploaded the resource.  This would most likely
     * be a unique ID from the client system.
     *
     * @MongoDB\String
     * @JMS\SerializedName("id")
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * An optional url referencing the internal user of the API
     * client who created the Resource.  For example, this could
     * be to a user's profile page of an external system.
     *
     * @MongoDB\String
     * @JMS\SerializedName("url")
     * @JMS\Type("string")
     */
    protected $url;

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
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;
    }

    /**
     * Get uri
     *
     * @return string $uri
     */
    public function getUrl()
    {
        return $this->url;
    }

}
