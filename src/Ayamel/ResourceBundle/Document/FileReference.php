<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;

/**
 * File reference object
 *
 * @MongoDB\EmbeddedDocument
 * @JMS\ExclusionPolicy("none")
 * 
 */
class FileReference {

    /**
     * @MongoDB\String
	 * @JMS\SerializedName("publicUri")
     */
	protected $publicUri;
	
    /**
     * @MongoDB\String
	 * @JMS\SerializedName("streamUri")
     */
	protected $streamUri;
	
    /**
     * @MongoDB\String
	 * @JMS\Exclude
	 * @JMS\SerializedName("internalUri")
     */
	protected $internalUri;

    /**
     * @MongoDB\Hash
     */
	protected $attributes;

	/**
	 * Create a reference from an internal file path
	 *
	 * @param string $internalUri 
	 * @return FileReference
	 */
	static public function createFromPath($internalUri) {
		$ref = new static();
		$ref->setInternalUri($internalUri);
		return $ref;
	}
	
	/**
	 * Create a reference to a public uri
	 *
	 * @param string $publicUri 
	 * @return FileReference
	 */
	static public function createFromPublicUri($publicUri) {
		$ref = new static();
		$ref->setPublicUri($publicUri);
		return $ref;
	}

    /**
     * Set all attributes
     *
     * @param hash $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get all attributes
     *
     * @return hash $attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
	
	/**
	 * Set an individual attribute by key for the attributes propery.
	 *
	 * @param string $key 
	 * @param mixed $val 
	 * @return self
	 */
	public function setAttribute($key, $val) {
		$this->attributes[$key] = $val;
		return $this;
	}
	
	/**
	 * Get an individual attribute by key, returns default value if not found
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getAttribute($key, $default = null) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
	}
	
	/**
	 * Remove an attribute by key if it exists.
	 *
	 * @param string $key 
	 * @return self
	 */
	public function removeAttribute($key) {
		if(isset($this->attributes[$key])) {
			unset($this->attributes[$key]);
		}
		
		return $this;
	}
	
	/**
	 * Return boolean if attribute exists
	 *
	 * @param string $key 
	 * @return boolean
	 */
	public function hasAttribute($key) {
		return isset($this->attributes[$key]);
	}

    /**
     * Set publicUri
     *
     * @param string $publicUri
     */
    public function setPublicUri($publicUri)
    {
        $this->publicUri = $publicUri;
    }

    /**
     * Get publicUri
     *
     * @return string $publicUri
     */
    public function getPublicUri()
    {
        return $this->publicUri;
    }

    /**
     * Set streamUri
     *
     * @param string $streamUri
     */
    public function setStreamUri($streamUri)
    {
        $this->streamUri = $streamUri;
    }

    /**
     * Get streamUri
     *
     * @return string $streamUri
     */
    public function getStreamUri()
    {
        return $this->streamUri;
    }

    /**
     * Set internalUri
     *
     * @param string $internalUri
     */
    public function setInternalUri($internalUri)
    {
        $this->internalUri = $internalUri;
    }

    /**
     * Get internalUri
     *
     * @return string $internalUri
     */
    public function getInternalUri()
    {
        return $this->internalUri;
    }
	
	/**
	 * Test if a given file reference instance is pointing to the same file as this file reference instance.
	 *
	 * @param FileReference $file 
	 * @return boolean
	 */
	public function equals(FileReference $file) {
		if($file->getInternalUri() && $this->getInternalUri()) {
			if($file->getInternalUri() == $this->getInternalUri()) {
				return true;
			}
		}
		
		if($file->getPublicUri() && $this->getPublicUri()) {
			if($file->getPublicUri() == $this->getPublicUri()) {
				return true;
			}
		}
		
		return false;
	}
}
