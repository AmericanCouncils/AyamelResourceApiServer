<?php

namespace Ayamel\ApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base API Event for Relations, also includes the subject and object Resources.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class RelationEvent extends Event
{
    protected $relation;
    protected $subject;
    protected $object;

    /**
     * Constructor requires the Relation and subject/object Resources.
     *
     * @param Relation $relation
     * @param Resource $subject
     * @param Resource $object
     */
    public function __construct(Relation $relation, Resource $subject, Resource $object)
    {
        $this->relation = $relation;
        $this->subject = $subject;
        $this->object = $object;
    }

    /**
     * Get the Relation associated with the event.
     *
     * @return Relation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Get the subject Resource associated with the event.
     *
     * @return Ayamel\ResourceBundle\Document\Resource;
     */
    public function getSubjectResource()
    {
        return $this->subject;
    }

    /**
     * Get the object Resource associated with the event.
     *
     * @return Ayamel\ResourceBundle\Document\Resource;
     */
    public function getObjectResource()
    {
        return $this->object;
    }
}
