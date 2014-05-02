<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * Origin object
 *
 * @MongoDB\EmbeddedDocument
 *
 */
class Origin
{
    use AutoGetterSetterTrait;

    /**
     * A creator for the Resource.  For example, in the case of
     * an image of a painting, the creator could be the original
     * artist who painted the picture.
     *
     * @MongoDB\String
     * @JMS\SerializedName("creator")
     * @JMS\Type("string")
     */
    protected $creator;

    /**
     * A location relevant to the Resource.  No specific required format.
     * In the case of an image of a painting, this could be the city and
     * country where the painting originated.
     *
     * @MongoDB\String
     * @JMS\SerializedName("location")
     * @JMS\Type("string")
     */
    protected $location;

    /**
     * A date relevant to the creation of the Resource.  No specific required
     * format.  In the case of an image of a painting, this could be the general
     * time period of when the painting was created.
     *
     * @MongoDB\String
     * @JMS\SerializedName("date")
     * @JMS\Type("string")
     */
    protected $date;

    /**
     * A description of the original format of the Resource.  No specific
     * required type.  In the case of an image of a painting, this could be
     * similar to the descriptions of format in a museum, such as "oil on canvas".
     *
     * @MongoDB\String
     * @JMS\SerializedName("format")
     * @JMS\Type("string")
     */
    protected $format;

    /**
     * Any relevant notes about the origin of the Resource or its
     * original content.
     *
     * @MongoDB\String
     * @JMS\SerializedName("note")
     * @JMS\Type("string")
     */
    protected $note;

    /**
     * If applicable, a valid public URI that points to the original
     * Resource content.
     *
     * @MongoDB\String
     * @JMS\SerializedName("uri")
     * @JMS\Type("string")
     */
    protected $uri;

}
