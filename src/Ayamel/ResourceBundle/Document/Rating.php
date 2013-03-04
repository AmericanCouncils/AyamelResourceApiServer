<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(db="ayamel", collection="ratings")
 * @JMS\ExclusionPolicy("none")
 */
class Rating
{

    /**
     * An optional weight for this Rating.
     *
     * @JMS\Type("integer")
     * @MongoDB\Int
     */
    public $weight;

    /**
     * The type of rater.
     *
     * @JMS\Type("string")
     * @MongoDB\String
     */
    public $raterType;

    /**
     * A difficulty rating on a scale of 1-100.
     *
     * @JMS\Type("integer")
     * @MongoDB\Int
     */
    public $difficulty;

    /**
     * Unique ID for Mongo
     *
     * @JMS\ReadOnly
     * @JMS\Type("string")
     * @MongoDB\Id
     */
    protected $id;

    /**
     * ID of Resource the rating is for
     *
     * @JMS\ReadOnly
     * @JMS\Type("string")
     * @MongoDB\String
     */
    protected $resourceId;

}
