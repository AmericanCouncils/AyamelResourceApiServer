<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(db="ayamel", collection="resources")
 * 
 */
class Resource {
    
    /**
     * @MongoDB\Id
     */
    protected $id;
    
    /**
     * @MongoDB\String
     */
    protected $title;
    
    /**
     * @MongoDB\String
     */
    protected $description;
    
    /**
     * @MongoDB\String
     */
    protected $keywords;
    
    /**
     * @MongoDB\String
     */
    protected $categories;
    
    /**
     * @MongoDB\String
     */
    protected $type;
    
    /**
     * @MongoDB\String
     */
    protected $contributer;
    
    /**
     * @MongoDB\String
     */
    protected $contributer_name;
    
    /**
     * @MongoDB\Boolean
     */
    protected $public = true;
        
    /**
     * @MongoDB\Hash
     */
    protected $l2_data;
    
    /**
     * @MongoDB\Date
     */
    protected $date_added;
    
    /**
     * @MongoDB\Date
     */
    protected $date_modified;
    
    /**
     * @MongoDB\Date
     */
    protected $date_deleted;
    
    /**
     * @MongoDB\String
     */
    protected $copyright;
    
    /**
     * @MongoDB\String
     */
    protected $status;
    
    /**
     * MongoDB\Id
     */
//    protected $content; //array of objects, variable type
    
    /**
     * @MongoDB\EmbedMany(targetDocument="Ayamel\ResourceBundle\Document\Relation")
     */
    protected $relations = array();
    
    public function __construct()
    {
        $this->relations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Get keywords
     *
     * @return string $keywords
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set categories
     *
     * @param string $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Get categories
     *
     * @return string $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set contributer
     *
     * @param string $contributer
     */
    public function setContributer($contributer)
    {
        $this->contributer = $contributer;
    }

    /**
     * Get contributer
     *
     * @return string $contributer
     */
    public function getContributer()
    {
        return $this->contributer;
    }

    /**
     * Set contributer_name
     *
     * @param string $contributerName
     */
    public function setContributerName($contributerName)
    {
        $this->contributer_name = $contributerName;
    }

    /**
     * Get contributer_name
     *
     * @return string $contributerName
     */
    public function getContributerName()
    {
        return $this->contributer_name;
    }

    /**
     * Set public
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return boolean $public
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set l2_data
     *
     * @param hash $l2Data
     */
    public function setL2Data($l2Data)
    {
        $this->l2_data = $l2Data;
    }

    /**
     * Get l2_data
     *
     * @return hash $l2Data
     */
    public function getL2Data()
    {
        return $this->l2_data;
    }

    /**
     * Set date_added
     *
     * @param date $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->date_added = $dateAdded;
    }

    /**
     * Get date_added
     *
     * @return date $dateAdded
     */
    public function getDateAdded()
    {
        return $this->date_added;
    }

    /**
     * Set date_modified
     *
     * @param date $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->date_modified = $dateModified;
    }

    /**
     * Get date_modified
     *
     * @return date $dateModified
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * Set date_deleted
     *
     * @param date $dateDeleted
     */
    public function setDateDeleted($dateDeleted)
    {
        $this->date_deleted = $dateDeleted;
    }

    /**
     * Get date_deleted
     *
     * @return date $dateDeleted
     */
    public function getDateDeleted()
    {
        return $this->date_deleted;
    }

    /**
     * Set copyright
     *
     * @param string $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * Get copyright
     *
     * @return string $copyright
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add relations
     *
     * @param Ayamel\ResourceBundle\Document\Relation $relations
     */
    public function addRelations(\Ayamel\ResourceBundle\Document\Relation $relations)
    {
        $this->relations[] = $relations;
    }

    /**
     * Get relations
     *
     * @return Doctrine\Common\Collections\Collection $relations
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
