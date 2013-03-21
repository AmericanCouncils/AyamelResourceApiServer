<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(db="ayamel", collection="resources")
 * @JMS\ExclusionPolicy("none")
 */
class Resource
{
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
    const STATUS_NORMAL = 'normal';

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
        'copyright' => 'string',
        'license' => 'string',
        'status' => 'string',
    );

    /**
     * The unique ID of the resource.
     *
     * @MongoDB\Id
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $id;

    /**
     * The title.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $title;

    /**
     * A short description.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $description;

    /**
     * A string of keywords for search.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $keywords;

    /**
     * An array of categories that apply to this resource.  Categories here are vetted
     * against a list of accepted and documented categories. (TODO)
     *
     * @MongoDB\Hash
     * @JMS\Type("array<string>")
     */
    protected $categories;

    /**
     * The generic type of resource.  Generic types are useful for sorting
     * search results into generally similar types of resources.
     *
     * Currently accepted types include:
     *
     * - **video** - The primary content is video.
     * - **audio** - The primary content is audio.
     * - **image** - The primary content is a static image.
     * - **document** - The primary content is a document meant for end-users.
     * - **archive** - The primary content is a collection of content in some archival format.
     * - **collection** - The primary content is a collection of other resources, which you can derive from the relations array.
     * - **data** - The primary content is in a data format intended for primary use by a program.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * An array of API Client IDs.  If present, only the specified clients will be allowed to view
     * the Resource object.
     *
     * If empty, the Resource is public and visible to all client systems.
     *
     * @MongoDB\Hash
     * @JMS\Type("array<string>")
     */
    protected $visibility;

    /**
     * The date the Resource was added into the database.
     *
     * @JMS\SerializedName("dateAdded")
     * @JMS\Type("DateTime")
     * @JMS\ReadOnly
     */
    protected $dateAdded;

    /**
     * The last time the Resource was modified.
     *
     * @MongoDB\Date
     * @JMS\SerializedName("dateModified")
     * @JMS\Type("DateTime")
     * @JMS\ReadOnly
     */
    protected $dateModified;

    /**
     * The date the Resource was deleted from the database, if applicable.
     *
     * @MongoDB\Date
     * @JMS\SerializedName("dateDeleted")
     * @JMS\Type("DateTime")
     * @JMS\ReadOnly
     */
    protected $dateDeleted;

    /**
     * Copyright text associated with the resource.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $copyright;

    /**
     * License type assocated with the resource.
     *
     * This must be provided before the Resource will be added
     * into the search index.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $license;

    /**
     * The status of the Resource, potential values include:
     *
     * - **normal** - No problems, and nothing scheduled to be done with the object.
     * - **awaiting_processing** - The Resource, or it's content, is in a queue to be processed and potentially modified.
     * - **awaiting_content** - The resource has no content associated with it yet.  Note that if a Resource is "awaiting_content" for more than two weeks, it will be automatically deleted.
     * - **processing** - The Resource, or its content, is currently being processed.  In this state, the Resource is locked and cannot be modified.
     * - **deleted** - The Resource and its content has been removed.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     * @JMS\ReadOnly
     */
    protected $status;

    /**
     * An optional object containing information about the origin of the Resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Origin")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Origin")
     */
    public $origin;

    /**
     * An object containing information about the API client that created the Resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Client")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Client")
     */
    public $client;

    /**
     * An object containing information about the primary content of the resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\ContentCollection")
     * @JMS\Type("Ayamel\ResourceBundle\Document\ContentCollection")
     * @JMS\ReadOnly
     */
    public $content;

    /**
     * An array of Relation objects that describe the relationship between this Resource and
     * other Resources.  Relations are critical to the search indexing process.
     *
     * @JMS\Type("array<Ayamel\ResourceBundle\Document\Relation>")
     * @JMS\ReadOnly
     */
    protected $relations;

    public function __construct()
    {
        //TODO: stop setting this in the constructor, if it's empty it should be null
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
    public function setCategories(array $categories = null)
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
     * Set visibility
     *
     * @param boolean $public
     */
    public function setVisibility(array $visibility = null)
    {
        $this->visibility = $visibility;
    }

    /**
     * Get visibility
     *
     * @return boolean $public
     */
    public function getVisibility()
    {
        return $this->visibility;
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
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
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
     * Set the origin
     *
     * @param Origin $origin
     */
    public function setOrigin(Origin $origin = null)
    {
        $this->origin = $origin;
    }

    /**
     * Get the origin
     *
     * @return Origin
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set the client
     *
     * @param Client $client
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Get the client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set relations
     *
     * @param  array Ayamel\ResourceBundle\Document\Relation $relations
     * @return self
     */
    public function setRelations(array $relations = null)
    {
        if ($relations) {
            $this->relations = new ArrayCollection();
            foreach ($relations as $relation) {
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
     * @param  Ayamel\ResourceBundle\Document\Relation $relation
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
     * @param  Relation $relation
     * @return self
     */
    public function removeRelation(Relation $relation)
    {
        $new = array();

        foreach ($this->relations as $instance) {
            if (!$instance->equals($relation)) {
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
     * Return whether or not the Resource is locked and should not be modified.
     *
     * @return boolean
     */
    public function isLocked()
    {
        return (self::STATUS_PROCESSING === $this->status);
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
     * @return true                     on success or array if validation fails
     */
    public function validate($return = false)
    {
        $errors = array();
        
        //enforce proper dates, unless this is being deleted
        if (Resource::STATUS_DELETED !== $this->getStatus()) {
            $date = new \DateTime();
            if (!$this->getId()) {
                $this->setDateAdded($date);
            }
        
            $this->setDateModified($date);
        }
        
        //check scalar fields
        foreach ($this->_validators as $field => $type) {
            //ignore null, that's how we unset/remove properties
            if ($this->$field !== null) {
                if (function_exists($func = "is_".$type)) {
                    if (!$func($this->$field)) {
                        $errors[] = sprintf("Field '%s' must be of type '%s'", $field, $type);
                    }
                }
            }
        }

        if (empty($errors)) {
            return true;
        }

        if ($return) {
            return $errors;
        }

        throw new \InvalidArgumentException(implode(". ", $errors));
    }

}
