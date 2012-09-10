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
     * A public URI where the file is accessible.
     * 
     * @MongoDB\String
     * @JMS\SerializedName("downloadUri")
     * @JMS\Type("string")
     */
    protected $downloadUri;
    
    /**
     * A public URI where the file can be streamed from.
     *
     * @MongoDB\String
     * @JMS\SerializedName("streamUri")
     * @JMS\Type("string")
     */
    protected $streamUri;
    
    /**
     * @MongoDB\String
     * @JMS\Exclude
     * @JMS\ReadOnly
     */
    protected $internalUri;
    
    /**
     * A string including the type and quality. in the 
     * format of `type`.`quality`.  For example: `transcoding.2`
     *
     * Valid types include:
     *
     * - **original** - If this is the original file.
     * - **transcoding** - If this file is a transcoding of the original in its entirety.
     * - **summary** - If this file is a partial transcoding of the original.
     *
     * Quality is an integer representing the relative quality.
     * 
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $representation;
    
    /**
     * The mime type of the file.
     * 
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $mime;

	/**
	 * @MongoDB\Boolean
     * @JMS\Exclude
     * @JMS\ReadOnly
	 */
	private $original;

    /**
     * A key/val hash of attributes, relevant to the `mime` of the file.
     * 
     * @MongoDB\Hash
     * @JMS\Type("array")
     */
    protected $attributes;

    /**
     * Create a reference from an internal file path
     *
     * @param string $internalUri 
     * @return FileReference
     */
    static public function createFromLocalPath($internalUri) {
        $ref = new static();
        $ref->setInternalUri($internalUri);
        return $ref;
    }
    
    /**
     * Create a reference to a public uri
     *
     * @param string $downloadUri 
     * @return FileReference
     */
    static public function createFromDownloadUri($downloadUri) {
        $ref = new static();
        $ref->setDownloadUri($downloadUri);
        return $ref;
    }
    
	/**
	 * Set boolean if this file reference is the original file content added.
	 *
	 * @param boolean $bool 
	 */
	public function setOriginal($bool = true) {
		$this->original = $bool;
	}
	
	/**
	 * Get whether or not this file reference was the original resource content.
	 *
	 * @return boolean
	 */
	public function getOriginal() {
		return $this->original;
	}
	
    /**
     * Get the string describing the files representation of the associated resource
     *
     * @return string
     */
    public function getRepresentation() {
        return $this->representation;
    }
    
    /**
     * Set the representation string in the format of "type;quality"
     * 
     * Type can be any of "original","summary", or "transcoding"
     * Quality can be a floating point number of up to 4 digits.
     *
     * @param string $representation 
     */
    public function setRepresentation($representation) {
        $this->representation = $representation;
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
	 * Merge an array of attributes into the current set, this will overwrite conflicting keys
	 * with the latest one received
	 *
	 * @param array $attrs 
	 */
	public function mergeAttributes(array $attrs) {
		$this->attributes = array_merge($this->attributes, $attrs);
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
     * Set the mime string
     *
     * @param string $mime 
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }
    
    /**
     * Returns mime string
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set downloadUri
     *
     * @param string $downloadUri
     */
    public function setDownloadUri($downloadUri)
    {
        $this->downloadUri = $downloadUri;
    }

    /**
     * Get downloadUri
     *
     * @return string $downloadUri
     */
    public function getDownloadUri()
    {
        return $this->downloadUri;
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
        if(($file->getInternalUri() && $this->getInternalUri()) && ($file->getInternalUri() == $this->getInternalUri())) {
            return true;
        }
        
        if(($file->getDownloadUri() && $this->getDownloadUri()) && ($file->getDownloadUri() == $this->getDownloadUri())) {
            return true;
        }
        
        return false;
    }
}
