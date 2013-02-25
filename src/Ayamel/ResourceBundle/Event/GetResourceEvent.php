<?php

namespace Ayamel\ResourceBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;

/**
 * Extension of ResourceEvent specifically for use when retrieving events.
 *
 * @author Evan Villemez
 */
class GetResourceEvent extends ResourceEvent
{
    /**
     * @var string - id of resource
     */
    protected $id;

    /**
     * @var string - array of parameters for retrieving a resource
     */
    protected $queryParams;

    /**
     * Constructor can optionally take a string id, or a Resource instance
     *
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        if ($id instanceof Resource) {
            parent::__construct($id);
        } else {
            $this->setResourceId($id);
        }
    }

    /**
     * Set the id of the resource to retrieve.
     *
     * @param string $id
     */
    public function setResourceId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the id of the resource being retrieved, if already set
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * Set array of parameters to use for retrieving a resource
     *
     * @param array $array
     */
    public function setQueryParameters(array $array)
    {
        $this->queryParams = $array;
    }

    /**
     * Get the array of query parameters
     *
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function setResource(Resource $resource)
    {
        $this->setResourceId($resource->getId());
        parent::setResource($resource);
    }

}
