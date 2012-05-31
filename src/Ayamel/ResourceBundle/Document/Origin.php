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
     * @MongoDB\String
     * @JMS\SerializedName("creator")
     */
    protected $creator;
    
    /**
     * @MongoDB\String
     * @JMS\SerializedName("location")
     */
    protected $location;
    
    /**
     * @MongoDB\Date
     * @JMS\SerializedName("date")
     */
	protected $date;
    
    
    /**
     * @MongoDB\String
     * @JMS\SerializedName("format")
     */
    protected $format;
    
    
    /**
     * @MongoDB\String
     * @JMS\SerializedName("note")
     */
    protected $note;
    
    /**
     * @MongoDB\String
     * @JMS\SerializedName("uri")
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
