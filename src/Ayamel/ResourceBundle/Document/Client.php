<?php
namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * API Client object, which can contain optional user-specific data.
 *
 * @MongoDB\EmbeddedDocument
 *
 * @package AyamelResourceBundle
 */
class Client
{
    use AutoGetterSetterTrait;

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
}
