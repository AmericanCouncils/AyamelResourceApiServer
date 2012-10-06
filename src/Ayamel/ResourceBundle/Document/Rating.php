<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(db="ayamel", collection="resources")
 * @JMS\ExclusionPolicy("none")
 */
class Rating
{
    
    /**
     * An optional weight for this Rating.
     *
     * @JMS/Type("integer")
     * @MongoDB/Int
     */
    public $weight;
    
    public $raterType;
    
    public $difficulty;
    
    
    
    
	/**
	 * Unique ID for Mongo
	 *
	 * @JMS/ReadOnly
	 * @MongoDB/Id
	 */
    protected $id;
    
    /**
     * ID of Resource the rating is for
     *
	 * @JMS/ReadOnly
	 * @JMS/Type("string")
	 * @MongoDB/String
     */
    protected $resourceId;
}
