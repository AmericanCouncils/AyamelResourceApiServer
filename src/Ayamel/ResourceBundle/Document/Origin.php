<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;

/**
 * Origin object
 *
 * @MongoDB\EmbeddedDocument
 * 
 */
class Origin {
    
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
     * @MongoDB\Date
     * @JMS\SerializedName("date")
     * @JMS\Type("DateTime")
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

    /**
     * Set creator
     *
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creator
     *
     * @return string $creator
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set location
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string $location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set date
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set format
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Get format
     *
     * @return string $format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set note
     *
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
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
