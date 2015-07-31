<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * File reference object
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("none")
 *
 */
class FileReference
{
    use AutoGetterSetterTrait;

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
     */
    protected $internalUri;

    /**
     * Size of the file in bytes
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    protected $bytes;

    /**
     * A string describing the representation.
     *
     * Valid values include:
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
     * An integer describing the relative quality.  Higher means higher quality relative to others.
     * Default quality is `1`.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    protected $quality;

    /**
     * The full mime string of the file, in as much detail as possible.  If not set, it will be set automatically
     * to the value of `mimeType`.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $mime;

    /**
     * The short mime type of the file, no extra information.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     * @JMS\SerializedName("mimeType")
     */
    protected $mimeType;

    /**
     * A key/val hash of attributes, relevant to the `mimeType` of the file.  For details on which attributes
     * are valid for a given mimeTime, please read through the documentation on the [project wiki](https://github.com/AmericanCouncils/AyamelResourceApiServer/wiki/Validation:-File-Attributes).
     *
     * @MongoDB\Hash
     * @JMS\Type("array")
     */
    protected $attributes;

    /**
     * Create a reference from an internal file path
     *
     * @param  string        $internalUri
     * @return FileReference
     */
    public static function createFromLocalPath($internalUri)
    {
        $ref = new static();
        $ref->setInternalUri($internalUri);

        return $ref;
    }

    /**
     * Create a reference to a public uri
     *
     * @param  string        $downloadUri
     * @return FileReference
     */
    public static function createFromDownloadUri($downloadUri)
    {
        $ref = new static();
        $ref->setDownloadUri($downloadUri);

        return $ref;
    }

    /**
     * Return whether or not the file is the original
     *
     * @param boolean $bool
     */
    public function isOriginal()
    {
        return ('original' === $this->representation);
    }

    /**
     * Merge an array of attributes into the current set, this will overwrite conflicting keys
     * with the latest one received
     *
     * @param array $attrs
     */
    public function mergeAttributes(array $attrs)
    {
        $this->attributes = array_merge($this->attributes, $attrs);
    }

    /**
     * Set an individual attribute by key for the attributes propery.
     *
     * @param  string $key
     * @param  mixed  $val
     * @return self
     */
    public function setAttribute($key, $val)
    {
        $this->attributes[$key] = $val;

        return $this;
    }

    /**
     * Get an individual attribute by key, returns default value if not found
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    /**
     * Remove an attribute by key if it exists.
     *
     * @param  string $key
     * @return self
     */
    public function removeAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * Return boolean if attribute exists
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Enforces certain values before persisting to the database
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function validate()
    {
        if (null === $this->quality) {
            $this->quality = 0;
        }

        if (null === $this->mime) {
            $this->mime = $this->mimeType;
        }
    }

    /**
     * Test if a given file reference instance is pointing to the same file as this file reference instance.
     *
     * @param  FileReference $file
     * @return boolean
     */
    public function equals(FileReference $file)
    {
        if (($file->getInternalUri() && $this->getInternalUri()) && ($file->getInternalUri() == $this->getInternalUri())) {
            return true;
        }

        if (($file->getDownloadUri() && $this->getDownloadUri()) && ($file->getDownloadUri() == $this->getDownloadUri())) {
            return true;
        }

        return false;
    }
}
