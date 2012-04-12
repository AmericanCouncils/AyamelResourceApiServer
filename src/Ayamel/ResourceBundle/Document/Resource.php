<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(db="ayamel", collection="resources")
 */
class Resource {
    
	/**
	 * Status when object has no content
	 */
	const STATUS_AWAITING_CONTENT = 'awaiting_content';

	/**
	 * Status when content is in queue to be processed
	 */
	const STATUS_AWAITING_PROCESSING = 'awaiting_processing';

	/**
	 * Status when content is currently being processed
	 */
	const STATUS_PROCESSING = 'processing';
	
	/**
	 * Status when content is processed and ok
	 */
	const STATUS_OK = 'ok';

	/**
	 * Status when object is deleted
	 */
	const STATUS_DELETED = 'deleted';
	
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
     * @MongoDB\Hash
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
	 * @JMS\SerializedName("contributerName")
     */
    protected $contributerName;
    
    /**
     * @MongoDB\Boolean
     */
    protected $public = true;
        
    /**
     * @MongoDB\Hash
	 * @JMS\SerializedName("l2Data")
     */
    protected $l2Data;
    
    /**
     * @MongoDB\Date
	 * @JMS\SerializedName("dateAdded")
     */
    protected $dateAdded;
    
    /**
     * @MongoDB\Date
	 * @JMS\SerializedName("dateModified")
     */
    protected $dateModified;
    
    /**
     * @MongoDB\Date
	 * @JMS\SerializedName("dateDeleted")
     */
    protected $dateDeleted;
    
    /**
     * @MongoDB\String
     */
    protected $copyright;
    
    /**
     * @MongoDB\String
     */
    protected $status;
    
    /**
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\ContentCollection")
     */
    public $content; //array of objects, variable type
    
    /**
     * @MongoDB\EmbedMany(targetDocument="Ayamel\ResourceBundle\Document\Relation")
     */
    protected $relations;
	    
    public function __construct()
    {
        $this->relations = new ArrayCollection();
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
     * Set contributerName
     *
     * @param string $contributerName
     */
    public function setContributerName($contributerName)
    {
        $this->contributerName = $contributerName;
    }

    /**
     * Get contributerName
     *
     * @return string $contributerName
     */
    public function getContributerName()
    {
        return $this->contributerName;
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
     * Set l2Data
     *
     * @param hash $l2Data
     */
    public function setL2Data(array $l2Data = null)
    {
        $this->l2Data = $l2Data;
    }

    /**
     * Get l2Data
     *
     * @return hash $l2Data
     */
    public function getL2Data()
    {
        return $this->l2Data;
    }
	
	/**
	 * Returns a specific l2Data field by key, or a default value if it doesn't exist
	 *
	 * @param string $key 
	 * @param mixed $default 
	 * @return mixed
	 */
	public function getL2Datum($key, $default = null) {
		return isset($this->l2Data[$key]) ? $this->l2Data[$key] : $default;
	}
	
	/*
	 * Add an individual l2Data property
	 *
	 * @param string $key 
	 * @param mixed $val 
	 * @return self
	 */
	public function addL2Datum($key, $val) {
		$this->l2Data[$key] = $val;
		return $this;
	}

	/**
	 * Return true/false if specific l2Data property exists
	 *
	 * @param string $key 
	 * @return boolean
	 */
	public function hasL2Datum($key) {
		return isset($this->l2Data[$key]);
	}
	
	/**
	 * remove specific l2Data property
	 *
	 * @param string $key 
	 * @return void
	 * @author Evan Villemez
	 */
	public function removeL2Datum($key) {
		if(isset($this->l2Data[$key])) {
			unset($$this->l2Data[$key]);
		}
		
		return $this;
	}

    /**
     * Set dateAdded
     *
     * @param date $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * Get dateAdded
     *
     * @return date $dateAdded
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set dateModified
     *
     * @param date $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;
    }

    /**
     * Get dateModified
     *
     * @return date $dateModified
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set dateDeleted
     *
     * @param date $dateDeleted
     */
    public function setDateDeleted($dateDeleted)
    {
        $this->dateDeleted = $dateDeleted;
    }

    /**
     * Get dateDeleted
     *
     * @return date $dateDeleted
     */
    public function getDateDeleted()
    {
        return $this->dateDeleted;
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
     * Set relations
     *
     * @param array Ayamel\ResourceBundle\Document\Relation $relations
     * @return self
     */
    public function setRelations(array $relations = null)
    {
		if($relations) {
			$this->relations = new ArrayCollection();
	        foreach($relations as $relation) {
				$this->addRelation($relation);
			}
		} else {
			$this->relations = new ArrayCollection();
		}
		
		return $this;
    }
	
    /**
     * Add a relation
     *
     * @param Ayamel\ResourceBundle\Document\Relation $relation
     * @return self
     */
    public function addRelation(Relation $relation)
    {
        $this->relations[] = $relation;
		return $this;
    }
	
	/**
	 * Remove an instance of a relation
	 *
	 * @param Relation $relation 
	 * @return self
	 */
	public function removeRelation(Relation $relation) {
		$new = array();
		
		foreach($this->relations as $instance) {
			if(!$instance->equals($relation)) {
				$new[] = $instance;
			}
		}

		$this->setRelations($new);
		return $this;
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

    /**
     * Set content collection
     *
     * @param Ayamel\ResourceBundle\Document\ContentCollection $content
     */
    public function setContent(ContentCollection $content = null)
    {
        $this->content = $content;
    }

    /**
     * Get content collection
     *
     * @return Ayamel\ResourceBundle\Document\ContentCollection $content
     */
    public function getContent()
    {
        return $this->content;
    }

}
