<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * Content container object, contains several types of fields for referencing the content of a resource object
 *
 * @MongoDB\EmbeddedDocument
 *
 */
class ContentCollection
{
    use AutoGetterSetterTrait;

    /**
     * @MongoDB\String
     * @JMS\SerializedName("canonicalUri")
     * @JMS\Type("string")
     */
    protected $canonicalUri;

    /**
     * Note that for now this is just a hash, in the future there will probably be a legitimate document.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\OEmbed")
     * @JMS\Type("Ayamel\ResourceBundle\Document\OEmbed")
     */
    protected $oembed;

    /**
     * Array of FileReference objects.
     *
     * @MongoDB\EmbedMany(targetDocument="Ayamel\ResourceBundle\Document\FileReference")
     * @JMS\Type("array<Ayamel\ResourceBundle\Document\FileReference>")
     */
    protected $files = [];

    /**
     * Set oembed fields
     *
     * @param hash $oembed
     */
    public function setOembed(OEmbed $oembed)
    {
        $this->oembed = $oembed;
    }

    /**
     * Set specific oembed field
     *
     * @param  string $key
     * @param  mixed  $val
     * @return self
     */
    public function setOembedKey($key, $val)
    {
        $this->oembed[$key] = $val;

        return $this;
    }

    /**
     * Get value for specific Oembed field, returning default if it doesn't exist
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getOembedKey($key, $default = null)
    {
        return isset($this->oembed[$key]) ? $this->oembed[$key] : $default;
    }

    /**
     * Remove a specific Oembed field if it's set
     *
     * @param  string $key
     * @return self
     */
    public function removeOembedKey($key)
    {
        if (isset($this->oembed[$key])) {
            unset($this->oembed[$key]);
        }

        return $this;
    }

    /**
     * Return true/false if specific oembed field exists
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasOembedKey($key)
    {
        return isset($this->oembed[$key]);
    }

    /**
     * Set files
     *
     * @param  array Ayamel\ResourceBundle\Document\FileReference $files
     * @return self
     */
    public function setFiles(array $files = null)
    {
        $this->files = [];

        if (!is_null($files)) {
            foreach ($files as $file) {
                $this->addFile($file);
            }
        }

        return $this;
    }

    /**
     * Add a relation
     *
     * @param  Ayamel\ResourceBundle\Document\Relation $file
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
     * @param  FileReference $file
     * @return self
     */
    public function removeFile(FileReference $file)
    {
        $new = [];

        //TODO: this... not so efficient, can be refactored later
        foreach ($this->files as $instance) {
            if (!$instance->equals($file)) {
                $new[] = $instance;
            }
        }

        $this->setFiles($new);

        return $this;
    }

    /**
     * Return boolean if a given file reference is contained in this content collection
     *
     * @param  FileReference $ref
     * @return booleah
     */
    public function hasFile(FileReference $ref)
    {
        foreach ($this->files as $file) {
            if ($ref->equals($file)) {
                return true;
            }
        }

        return false;
    }
}
