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
	 * Array of scalar property type validators, because PHP sucks and doesn't do scalar type hinting.  This is used in the `validate()` method.
	 *
	 * @JMS\Exclude
	 * 
	 * @var array
	 */
	protected $_validators = array(
		'title' => 'string',
		'description' => 'string',
		'keywords' => 'string',
		'type' => 'string',
		'contributer' => 'string',
		'contributerName' => 'string',
		'public' => 'bool',
		'copyright' => 'string',
		'license' => 'string',
		'status' => 'string',
	);
	
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
	protected $license = "Creative Commons";
    
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
     * @param array $categories
     */
    public function setCategories(array $categories)
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
    public function setDateAdded(\DateTime $dateAdded = null)
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
    public function setDateModified(\DateTime $dateModified = null)
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
    public function setDateDeleted(\DateTime $dateDeleted = null)
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
	 * Set license field
	 *
	 * @param string $license 
	 */
	public function setLicense($license) {
		return $this->license;
	}
	
	/**
	 * Get license
	 *
	 * @return string
	 */
	public function getLicense() {
		return $this->license;
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

	/**
	 * Validation method, because PHP sucks and can't do scalar type hinting.  Called automatically by Mongodb ODM before create/update operations.
	 * 
	 * Note that this validation is only for checking that values are of a certain type for a given field.  This validation has nothing to do with whether or not
	 * a client has sent acceptable input via an api.
	 *
	 * @MongoDB\PrePersist
	 * @MongoDB\PreUpdate
	 * 
	 * @param $return - whether or not to return errors, or throw exception
	 * @throws InvalidArgumentException if $return is false
	 * @return true on success or array if validation fails
	 */
	public function validate($return = false) {
		$errors = array();
		
		//check scalar fields
		foreach($this->_validators as $field => $type) {	
			//ignore null, that's how we unset/remove properties
			if($this->$field !== null) {
				if(function_exists($func = "is_".$type)) {
					if(!$func($this->$field)) {
						$errors[] = sprintf("Field '%s' must be of type '%s'", $field, $type);
					}
				}
			}
		}
		
		if(empty($errors)) {
			return true;
		}
		
		if($return) {
			return $errors;
		}
		
		throw new \InvalidArgumentException(implode(". ", $errors));
	}

}
