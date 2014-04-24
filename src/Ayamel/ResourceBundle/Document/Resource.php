<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * Base Resource persistence class
 *
 * @MongoDB\Document(
 *      collection="resources",
 *      repositoryClass="Ayamel\ResourceBundle\Repository\ResourceRepository"
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class Resource
{
    use AutoGetterSetterTrait;

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
     * The unique ID of the resource.
     *
     * @MongoDB\Id
     * @JMS\Type("string")
     * @JMS\Groups({"search-decode"})
     */
    private $id;

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
     * A comma-delimited string of keywords for search.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    protected $keywords;

    /**
     * An object containing arrays of language codes.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Languages")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Languages")
     */
    public $languages = null;

    /**
     * An array of categories that apply to the content of the Resource.  Categories here are vetted
     * against a list of accepted and documented categories.
     *
     *  //TODO: document valid values
     *
     * @MongoDB\Collection
     * @JMS\SerializedName("subjectDomains")
     * @JMS\Type("array<string>")
     */
    protected $subjectDomains;

    /**
     * An array of categories that apply to the linguistic properties of the Resource.  Categories here are vetted
     * against a list of accepted and documented categories.
     *
     *  //TODO: document valid values
     *
     * @MongoDB\Collection
     * @JMS\SerializedName("functionalDomains")
     * @JMS\Type("array<string>")
     */
    protected $functionalDomains;

    /**
     * An array of language registers present in the Resource.  Valid values include *formal*,
     * *casual*, *intimate*, *static* and *consultative*.
     *
     * @MongoDB\Collection
     * @JMS\Type("array<string>")
     */
    protected $registers;

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
     * Whether or not the Resource is a sequence of other Resources.  Only Resources of type
     * *video, audio, image* and *document* can be considered sequences.  Sequences are played
     * in the embedded player as if they were one Resource.
     *
     * @MongoDB\Boolean
     * @JMS\Type("boolean")
     */
    protected $sequence;

    /**
     * An array of API Client IDs.  If present, only the specified clients will be allowed to view
     * the Resource object.
     *
     * If empty, the Resource is public and visible to all client systems.
     *
     * @MongoDB\Collection
     * @JMS\Type("array<string>")
     */
    protected $visibility;

    /**
     * The date the Resource was added into the database.
     *
     * @MongoDB\Date
     * @JMS\SerializedName("dateAdded")
     * @JMS\Type("DateTime<'U'>")
     * @JMS\Groups({"search-decode"})
     */
    protected $dateAdded;

    /**
     * The last time the Resource was modified.
     *
     * @MongoDB\Date
     * @JMS\SerializedName("dateModified")
     * @JMS\Type("DateTime<'U'>")
     * @JMS\Groups({"search-decode"})
     */
    protected $dateModified;

    /**
     * The date the Resource was deleted from the database, if applicable.
     *
     * @MongoDB\Date
     * @JMS\SerializedName("dateDeleted")
     * @JMS\Type("DateTime<'U'>")
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
     * @JMS\Groups({"search-decode"})
     */
    protected $status;

    /**
     * An optional object containing information about the origin of the Resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Origin")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Origin")
     */
    public $origin = null;

    /**
     * An object containing information about the API client that created the object.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\ClientUser")
     * @JMS\SerializedName("clientUser")
     * @JMS\Type("Ayamel\ResourceBundle\Document\ClientUser")
     */
    public $clientUser = null;

    /**
     * An object containing information about the API client that created the Resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\Client")
     * @JMS\Type("Ayamel\ResourceBundle\Document\Client")
     * @JMS\Groups({"search-decode"})
     */
    public $client = null;

    /**
     * An object containing information about the primary content of the resource.
     *
     * @MongoDB\EmbedOne(targetDocument="Ayamel\ResourceBundle\Document\ContentCollection")
     * @JMS\Type("Ayamel\ResourceBundle\Document\ContentCollection")
     * @JMS\Groups({"search-decode"})
     */
    public $content = null;

    /**
     * An array of Relation objects that describe the relationship between this Resource and
     * other Resources.  Relations are critical to the search indexing process.
     *
     * @JMS\Type("array<Ayamel\ResourceBundle\Document\Relation>")
     * @JMS\Groups({"search-decode"})
     */
    protected $relations = [];

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
     * Set dateModified
     *
     * @param date $dateModified
     */
    public function setDateModified(\DateTime $dateModified = null)
    {
        $this->dateModified = $dateModified;
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
     * Set languages
     *
     * @param Languages $langs
     */
    public function setLanguages(Languages $langs = null)
    {
        $this->languages = $langs;
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
     * Set the client
     *
     * @param Client $client
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Set the optional client user
     *
     * @param ClientUser $user
     */
    public function setClientUser(ClientUser $user = null)
    {
        $this->clientUser = $user;
    }

    /**
     * Set relations
     *
     * @param  array Ayamel\ResourceBundle\Document\Relation $relations
     * @return self
     */
    public function setRelations(array $relations = null)
    {
        $this->relations = [];

        if (!is_null($relations)) {
            foreach ($relations as $relation) {
                $this->addRelation($relation);
            }
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
        $newRels = [];

        foreach ($this->relations as $r) {
            if ($r->getId() != $relation->getId()) {
                $newRels[] = $r;
            }
        }

        return $this->setRelations($newRels);
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
     * Return whether or not the Resource is locked and should not be modified.
     *
     * @return boolean
     */
    public function isLocked()
    {
        return (self::STATUS_PROCESSING === $this->status);
    }

    /**
     * Return whether or not the resource has been deleted
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return (self::STATUS_DELETED === $this->status);
    }

    /**
     * Validation method ensure that date fields are set properly.
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function validate()
    {
        if (!$this->isDeleted()) {
            $date = new \DateTime();
            if (!$this->getId()) {
                $this->setDateAdded($date);
            }

            $this->setDateModified($date);
        }

        //make sure clients can't lock themselves out of their own resources
        if ($this->getVisibility() && $this->getClient()) {
            if (!in_array($this->getClient()->getId(), $this->visibility)) {
                $this->visibility[] = $this->getClient()->getId();
            }
        }
    }

}
