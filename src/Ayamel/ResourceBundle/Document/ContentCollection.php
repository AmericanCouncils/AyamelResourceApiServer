<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Content container object, contains several types of fields for referencing the content of a resource object
 *
 * @MongoDB\EmbeddedDocument
 * 
 */
class ContentCollection {
    
    /**
     * @MongoDB\String
     * @JMS\SerializedName("canonicalUri")
     */
    protected $canonicalUri;

    /**
     * @MongoDB\Hash
     */
    protected $oembed;
    
    /**
     * @MongoDB\EmbedMany(targetDocument="Ayamel\ResourceBundle\Document\FileReference")
     */
    protected $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * Set canonicalUri
     *
     * @param string $canonicalUri
     */
    public function setCanonicalUri($canonicalUri)
    {
        $this->canonicalUri = $canonicalUri;
    }

    /**
     * Get canonicalUri
     *
     * @return string $canonicalUri
     */
    public function getCanonicalUri()
    {
        return $this->canonicalUri;
    }

    /**
     * Set oembed fields
     *
     * @param hash $oembed
     */
    public function setOembed(array $oembed)
    {
        $this->oembed = $oembed;
    }

    /**
     * Get Oembed fields
     *
     * @return hash $oembed
     */
    public function getOembed()
    {
        return $this->oembed;
    }
    
    /**
     * Set specific oembed field
     *
     * @param string $key 
     * @param mixed $val 
     * @return self
     */
    public function setOembedKey($key, $val) {
        $this->oembed[$key] = $val;
        return $this;
    }
    
    /**
     * Get value for specific Oembed field, returning default if it doesn't exist
     *
     * @param string $key 
     * @param mixed $default 
     * @return mixed
     */
    public function getOembedKey($key, $default = null) {
        return isset($this->oembed[$key]) ? $this->oembed[$key] : $default;
    }
    
    /**
     * Remove a specific Oembed field if it's set
     *
     * @param string $key 
     * @return self
     */
    public function removeOembedKey($key) {
        if(isset($this->oembed[$key])) {
            unset($this->oembed[$key]);
        }
        
        return $this;
    }
    
    /**
     * Return true/false if specific oembed field exists
     *
     * @param string $key 
     * @return boolean
     */
    public function hasOembedKey($key) {
        return isset($this->oembed[$key]);
    }
    
    /**
     * Set files
     *
     * @param array Ayamel\ResourceBundle\Document\FileReference $files
     * @return self
     */
    public function setFiles(array $files = null)
    {
        if($files !== null) {
            $this->files = new ArrayCollection();
            foreach($files as $file) {
                $this->addFile($file);
            }
        } else {
            $this->files = new ArrayCollection();
        }
        
        return $this;
    }

    /**
     * Add a relation
     *
     * @param Ayamel\ResourceBundle\Document\Relation $file
     * @return self
     */
    public function addFile(FileReference $file)
    {
        $this->files[] = $file;
        return $this;
    }
    
    /**
     * Remove an instance of a relation
     *
     * @param FileReference $file 
     * @return self
     */
    public function removeFile(FileReference $file) {
        $new = array();
        
        //TODO: this... not so efficient, can be refactored later
        foreach($this->files as $instance) {
            if(!$instance->equals($file)) {
                $new[] = $instance;
            }
        }

        $this->setFiles($new);
        return $this;
    }
    
    /**
     * Return boolean if a given file reference is contained in this content collection
     *
     * @param FileReference $ref 
     * @return booleah
     */
    public function hasFile(FileReference $ref) {
        foreach($this->files as $files) {
            if($ref->equals($file)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get files
     *
     * @return Doctrine\Common\Collections\Collection $files
     */
    public function getFiles()
    {
        return $this->files;
    }

}
